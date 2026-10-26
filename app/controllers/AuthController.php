<?php

class AuthController extends Controller {

    private $adminModel;
    private $mayorModel;

    public function __construct() {
        $this->adminModel = $this->model('Admin');
        $this->mayorModel = $this->model('ClassMayor');
    }

    public function index() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        $this->login();
    }

    public function login() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        $this->view('auth/login');
    }

    public function forgotpassword() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        $this->view('auth/forgot_password');
    }

    /**
     * POST /auth/sendotp
     * Generates OTP, saves to DB, and emails it via Gmail SMTP.
     */
    public function sendotp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/forgotpassword');
            return;
        }

        $this->validateCSRF();

        $rawEmail = $_POST['email'] ?? '';
        $email = InputValidator::validateEmail($rawEmail);

        if (!$email) {
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->redirect('/auth/forgotpassword');
            return;
        }

        // Check if the email exists in any user table
        $emailExists = false;
        $userType = '';
        $db = Database::getInstance()->getConnection();

        $admin = $this->adminModel->findByEmail($email);
        if ($admin) { $emailExists = true; $userType = 'admin'; }

        if (!$emailExists) {
            $mayor = $this->mayorModel->findByEmail($email);
            if ($mayor) { $emailExists = true; $userType = 'mayor'; }
        }

        if (!$emailExists) {
            $stmt = $db->prepare("SELECT student_id FROM student WHERE email = :email AND profile_status = 'Active'");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) { $emailExists = true; $userType = 'student'; }
        }

        if (!$emailExists) {
            $this->setFlash('error', 'No account found with this email address.');
            $this->redirect('/auth/forgotpassword');
            return;
        }

        // Rate limit: max 3 OTP requests per email in 15 minutes
        $stmt = $db->prepare(
            "SELECT COUNT(*) as cnt FROM password_resets 
             WHERE email = :email AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stmt->execute([':email' => $email]);
        $count = (int) $stmt->fetch()['cnt'];

        if ($count >= 3) {
            $this->setFlash('error', 'Too many OTP requests. Please wait before trying again.');
            $this->redirect('/auth/forgotpassword');
            return;
        }

        // Invalidate previous OTPs for this email
        $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE email = :email AND used = 0");
        $stmt->execute([':email' => $email]);

        // Generate and store OTP
        $otpCode = generateOTP();
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);

        $stmt = $db->prepare(
            "INSERT INTO password_resets (email, otp_code, otp_hash, user_type, expires_at) VALUES (:email, :otp, :hash, :utype, DATE_ADD(NOW(), INTERVAL :expiry SECOND))"
        );
        $stmt->execute([
            ':email'   => $email,
            ':otp'     => $otpCode,
            ':hash'    => $otpHash,
            ':utype'   => $userType,
            ':expiry'  => OTP_EXPIRY,
        ]);

        // Send the OTP email
        try {
            sendOtpEmail($email, $otpCode);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to send OTP email. Please try again later.');
            $this->redirect('/auth/forgotpassword');
            return;
        }

        // Store email in session for the verify step
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_user_type'] = $userType;
        $_SESSION['otp_sent_at'] = time();

        $this->setFlash('success', 'A verification code has been sent to your email.');
        $this->redirect('/auth/verifyotp');
    }

    /**
     * GET /auth/verifyotp — show the OTP input form
     * POST /auth/verifyotp — verify the submitted OTP
     */
    public function verifyotp() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }

        if (!isset($_SESSION['otp_email'])) {
            $this->redirect('/auth/forgotpassword');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $email = $_SESSION['otp_email'];
            $otpInput = trim($_POST['otp'] ?? '');

            if (strlen($otpInput) !== OTP_LENGTH) {
                $this->setFlash('error', 'Please enter a valid ' . OTP_LENGTH . '-digit code.');
                $this->redirect('/auth/verifyotp');
                return;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM password_resets 
                 WHERE email = :email AND otp_code = :otp AND used = 0 AND expires_at > NOW()
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([':email' => $email, ':otp' => $otpInput]);
            $record = $stmt->fetch();

            if (!$record) {
                $this->setFlash('error', 'Invalid or expired code. Please try again.');
                $this->redirect('/auth/verifyotp');
                return;
            }

            // Mark OTP as used
            $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
            $stmt->execute([':id' => $record['id']]);

            // Grant access to reset password
            $_SESSION['otp_verified'] = true;

            $this->redirect('/auth/resetpassword');
            return;
        }

        // GET — show the verify form
        $this->view('auth/verify_otp', [
            'email' => $_SESSION['otp_email'],
        ]);
    }

    /**
     * POST /auth/resendotp — resend a new OTP code
     */
    public function resendotp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/forgotpassword');
            return;
        }

        $this->validateCSRF();

        if (!isset($_SESSION['otp_email'])) {
            $this->redirect('/auth/forgotpassword');
            return;
        }

        // Cooldown: 60 seconds between resends
        if (isset($_SESSION['otp_sent_at']) && (time() - $_SESSION['otp_sent_at']) < 60) {
            $this->setFlash('error', 'Please wait before requesting a new code.');
            $this->redirect('/auth/verifyotp');
            return;
        }

        $email = $_SESSION['otp_email'];
        $db = Database::getInstance()->getConnection();

        // Rate limit
        $stmt = $db->prepare(
            "SELECT COUNT(*) as cnt FROM password_resets 
             WHERE email = :email AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stmt->execute([':email' => $email]);
        if ((int) $stmt->fetch()['cnt'] >= 5) {
            $this->setFlash('error', 'Too many requests. Please try again later.');
            $this->redirect('/auth/verifyotp');
            return;
        }

        // Invalidate old OTPs
        $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE email = :email AND used = 0");
        $stmt->execute([':email' => $email]);

        $otpCode = generateOTP();
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);
        $userType = $_SESSION['otp_user_type'] ?? 'student';

        $stmt = $db->prepare(
            "INSERT INTO password_resets (email, otp_code, otp_hash, user_type, expires_at) VALUES (:email, :otp, :hash, :utype, DATE_ADD(NOW(), INTERVAL :expiry SECOND))"
        );
        $stmt->execute([':email' => $email, ':otp' => $otpCode, ':hash' => $otpHash, ':utype' => $userType, ':expiry' => OTP_EXPIRY]);

        try {
            sendOtpEmail($email, $otpCode);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to send email. Please try again.');
            $this->redirect('/auth/verifyotp');
            return;
        }

        $_SESSION['otp_sent_at'] = time();
        $this->setFlash('success', 'A new verification code has been sent.');
        $this->redirect('/auth/verifyotp');
    }

    /**
     * GET /auth/resetpassword — show new-password form
     * POST /auth/resetpassword — save the new password
     */
    public function resetpassword() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }

        if (!isset($_SESSION['otp_email']) || empty($_SESSION['otp_verified'])) {
            $this->redirect('/auth/forgotpassword');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';

            if (strlen($password) < 8) {
                $this->setFlash('error', 'Password must be at least 8 characters.');
                $this->redirect('/auth/resetpassword');
                return;
            }

            if ($password !== $confirm) {
                $this->setFlash('error', 'Passwords do not match.');
                $this->redirect('/auth/resetpassword');
                return;
            }

            $email = $_SESSION['otp_email'];
            $hash  = password_hash($password, PASSWORD_BCRYPT);
            $db    = Database::getInstance()->getConnection();
            $updated = false;

            // Update in all user tables
            $stmt = $db->prepare("UPDATE admin SET password_hash = :hash WHERE email = :email");
            $stmt->execute([':hash' => $hash, ':email' => $email]);
            if ($stmt->rowCount() > 0) $updated = true;

            $stmt = $db->prepare("UPDATE class_mayor SET password_hash = :hash WHERE email = :email");
            $stmt->execute([':hash' => $hash, ':email' => $email]);
            if ($stmt->rowCount() > 0) $updated = true;

            $stmt = $db->prepare("UPDATE student SET password_hash = :hash WHERE email = :email AND profile_status = 'Active'");
            $stmt->execute([':hash' => $hash, ':email' => $email]);
            if ($stmt->rowCount() > 0) $updated = true;

            // Cleanup session OTP data
            unset($_SESSION['otp_email'], $_SESSION['otp_verified'], $_SESSION['otp_sent_at']);

            // Cleanup expired/used OTPs from DB
            $db->exec("DELETE FROM password_resets WHERE used = 1 OR expires_at < NOW()");

            if ($updated) {
                $this->setFlash('success', 'Password reset successfully! Please log in with your new password.');
            } else {
                $this->setFlash('error', 'Something went wrong. Please try again.');
            }

            $this->redirect('/auth/login');
            return;
        }

        // GET — show the reset form
        $this->view('auth/reset_password');
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }

        $this->validateCSRF();

        $rawEmail = $_POST['email'] ?? '';
        $email    = InputValidator::validateEmail($rawEmail);
        $password = $_POST['password'] ?? '';

        $ip = $_SERVER['REMOTE_ADDR'];
        $lockout = $this->checkLoginAttempts($ip);
        if ($lockout !== true) {
            $msg = 'Too many failed attempts. Please wait ' . $lockout . ' seconds before trying again.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg, 'locked' => true, 'retry_after' => $lockout], 429);
                return;
            }
            $this->setFlash('error', $msg);
            $this->redirect('/auth/login');
            return;
        }

        if (!$email && !empty($rawEmail)) {
            $attemptsLeft = $this->logFailedAttempt($ip, $rawEmail);
            if ($attemptsLeft === 0) {
                $lockMsg = 'Too many failed attempts. Please wait 20 seconds before trying again.';
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'field' => 'email', 'field_message' => 'No account found with this email.', 'message' => $lockMsg, 'locked' => true, 'retry_after' => 20, 'attempts_left' => 0], 429);
                    return;
                }
                $this->setFlash('error', $lockMsg);
                $this->redirect('/auth/login');
                return;
            }
            $attemptsNotice = $attemptsLeft . ' attempt' . ($attemptsLeft === 1 ? '' : 's') . ' remaining.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'field' => 'email', 'field_message' => 'No account found with this email.', 'message' => $attemptsNotice, 'attempts_left' => $attemptsLeft]);
                return;
            }
            $this->setFlash('error', 'No account found with this email. ' . $attemptsNotice);
            $this->redirect('/auth/login');
            return;
        }

        if (!$email || empty($password)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please enter valid credentials.']);
                return;
            }
            $this->setFlash('error', 'Please enter valid credentials.');
            $this->redirect('/auth/login');
            return;
        }

        $userId       = null;
        $userName     = null;
        $userRole     = null;
        $userAvatar   = null;
        $emailExists  = false;

        $admin = $this->adminModel->findByEmail($email);
        if ($admin) {
            $emailExists = true;
            if (password_verify($password, $admin['password_hash'])) {
                $userId   = $admin['admin_id'];
                $userName = $admin['name'];
                $userRole = 'admin';
            }
        }

        if (!$userId) {
            $mayor = $this->mayorModel->findByEmail($email);
            if ($mayor) {
                $emailExists = true;
                if (password_verify($password, $mayor['password_hash'])) {
                    $userId   = $mayor['mayor_id'];
                    $userName = $mayor['name'];
                    $userRole = 'mayor';
                    $_SESSION['class_id']     = $mayor['class_id'];
                    $_SESSION['section_name'] = $mayor['section_name'];
                }
            }
        }

        if (!$userId) {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM student WHERE email = :email AND profile_status = 'Active'");
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch();
            if ($student) {
                $emailExists = true;
                if (password_verify($password, $student['password_hash'] ?? '')) {
                    $userId   = $student['student_id'];
                    $userName = $student['first_name'] . ' ' . $student['last_name'];
                    $userRole = 'student';
                    $userAvatar = $student['profile_image'] ?? null;
                }
            }
        }

        if ($userId) {
            $this->clearLoginAttempts($ip);
            session_regenerate_id(true);
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_role'] = $userRole;
            $_SESSION['user_name'] = $userName;
            $_SESSION['user_avatar'] = $userAvatar ?? null;

            if ($this->isAjax()) {
                $this->json(['success' => true, 'role' => $userRole, 'redirect' => $this->getRoleRedirect($userRole)]);
                return;
            }
            $this->redirectByRole();
        } else {
            $attemptsLeft = $this->logFailedAttempt($ip, $email);

            if (!$emailExists) {
                $field    = 'both';
                $fieldMsg = 'Invalid email or password.';
            } else {
                $field    = 'password';
                $fieldMsg = 'Incorrect password. Please try again.';
            }

            if ($attemptsLeft !== null && $attemptsLeft > 0) {
                $attemptsNotice = $attemptsLeft . ' attempt' . ($attemptsLeft === 1 ? '' : 's') . ' remaining.';
            } else {
                $attemptsNotice = '';
            }

            if ($attemptsLeft === 0) {
                $lockMsg = 'Too many failed attempts. Please wait 20 seconds before trying again.';
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => $lockMsg, 'field_message' => $fieldMsg, 'field' => $field, 'locked' => true, 'retry_after' => 20, 'attempts_left' => 0], 429);
                    return;
                }
                $this->setFlash('error', $lockMsg);
                $this->redirect('/auth/login');
                return;
            }

            if ($this->isAjax()) {
                $this->json(['success' => false, 'field_message' => $fieldMsg, 'message' => $attemptsNotice, 'field' => $field, 'attempts_left' => $attemptsLeft]);
                return;
            }
            $this->setFlash('error', $fieldMsg . ($attemptsNotice ? ' ' . $attemptsNotice : ''));
            $this->redirect('/auth/login');
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        session_start();
        $this->setFlash('success', 'You have been logged out.');
        $this->redirect('/auth/login');
    }

    private function redirectByRole() {
        $role = getUserRole();
        $this->redirect($this->getRoleRedirect($role));
    }

    private function getRoleRedirect($role) {
        switch ($role) {
            case 'admin':   return '/dashboard';
            case 'mayor':   return '/scan';
            case 'student': return '/studentview';
            default:        return '/auth/login';
        }
    }

    private function checkLoginAttempts($ip) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "SELECT attempt_time FROM login_attempts
             WHERE ip_address = :ip
             ORDER BY attempt_time DESC LIMIT 3"
        );
        $stmt->execute([':ip' => $ip]);
        $rows = $stmt->fetchAll();

        if (count($rows) >= 3) {
            $thirdAttemptTime = $rows[2]['attempt_time'];
            $stmt2 = $db->prepare(
                "SELECT GREATEST(0, 20 - TIMESTAMPDIFF(SECOND, :locktime, NOW())) AS wait_seconds"
            );
            $stmt2->execute([':locktime' => $thirdAttemptTime]);
            $wait = $stmt2->fetch();
            $seconds = (int)($wait['wait_seconds'] ?? 0);

            if ($seconds > 0) {
                return $seconds;
            }

            $this->clearLoginAttempts($ip);
        }
        return true;
    }

    private function logFailedAttempt($ip, $email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (:ip, :email, NOW())"
        );
        $stmt->execute([':ip' => $ip, ':email' => $email]);

        $stmt2 = $db->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = :ip"
        );
        $stmt2->execute([':ip' => $ip]);
        $result = $stmt2->fetch();
        $used = (int)$result['attempts'];
        return max(0, 3 - $used);
    }

    private function clearLoginAttempts($ip) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
        $stmt->execute([':ip' => $ip]);
    }
}