<?php

class AuthController extends Controller {

    private $adminModel;
    private $mayorModel;
    private $studentModel;
    private $passwordResetModel;

    public function __construct() {
        $this->adminModel = $this->model('Admin');
        $this->mayorModel = $this->model('ClassMayor');
        $this->studentModel = $this->model('Student');
        $this->passwordResetModel = $this->model('PasswordReset');
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
                }
            }
        }

        if ($userId) {
            $this->clearLoginAttempts($ip);
            session_regenerate_id(true);
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_role'] = $userRole;
            $_SESSION['user_name'] = $userName;

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

    public function forgotPassword() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        $this->view('auth/forgot_password');
    }

    public function sendResetLink() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/forgotPassword');
            return;
        }
        $this->validateCSRF();

        $rawEmail = trim((string)($_POST['email'] ?? ''));
        $email = InputValidator::validateEmail($rawEmail);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $genericMessage = 'If an account exists for that email, a password reset link has been sent.';

        $this->passwordResetModel->logResetAttempt($rawEmail, $ip);
        if ($this->passwordResetModel->isRateLimited($ip, $rawEmail)) {
            $this->setFlash('success', $genericMessage);
            $this->redirect('/auth/forgotPassword');
            return;
        }

        if ($email && $this->isGmailAddress($email)) {
            $user = $this->findResetUserByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60));

                $this->passwordResetModel->invalidateActiveTokens($user['user_type'], (int)$user['user_id']);
                $this->passwordResetModel->createToken(
                    $user['user_type'],
                    (int)$user['user_id'],
                    $user['email'],
                    $tokenHash,
                    $expiresAt,
                    $ip
                );

                $resetUrl = $this->buildAbsoluteUrl('auth/resetPassword?token=' . urlencode($token));
                sendPasswordResetEmail($user['email'], $user['name'], $resetUrl);
            }
        }

        $this->setFlash('success', $genericMessage);
        $this->redirect('/auth/forgotPassword');
    }

    public function resetPassword() {
        if (isLoggedIn()) {
            $this->redirectByRole();
            return;
        }

        $token = trim((string)($_GET['token'] ?? ''));
        if (!$this->isValidTokenFormat($token) || !$this->passwordResetModel->isValidToken($token)) {
            $this->setFlash('error', 'This password reset link is invalid or has expired.');
            $this->redirect('/auth/forgotPassword');
            return;
        }

        $this->view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/forgotPassword');
            return;
        }
        $this->validateCSRF();

        $token = trim((string)($_POST['token'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if (!$this->isValidTokenFormat($token)) {
            $this->setFlash('error', 'This password reset link is invalid or has expired.');
            $this->redirect('/auth/forgotPassword');
            return;
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('/auth/resetPassword?token=' . urlencode($token));
            return;
        }

        $passwordCheck = InputValidator::validatePassword($password);
        if ($passwordCheck !== true) {
            $this->setFlash('error', $passwordCheck);
            $this->redirect('/auth/resetPassword?token=' . urlencode($token));
            return;
        }

        $tokenData = $this->passwordResetModel->findByToken($token);
        if (!$tokenData || !$this->passwordResetModel->consumeToken($token)) {
            $this->setFlash('error', 'This password reset link is invalid or has expired.');
            $this->redirect('/auth/forgotPassword');
            return;
        }

        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $updated = $this->updateUserPasswordHash($tokenData['user_type'], (int)$tokenData['user_id'], $newHash);

        if (!$updated) {
            $this->setFlash('error', 'Unable to reset password. Please request a new reset link.');
            $this->redirect('/auth/forgotPassword');
            return;
        }

        $this->passwordResetModel->invalidateActiveTokens($tokenData['user_type'], (int)$tokenData['user_id']);
        $this->setFlash('success', 'Your password has been reset. You may now sign in.');
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

    private function isGmailAddress($email) {
        return (bool)preg_match('/@gmail\.com$/i', $email);
    }

    private function isValidTokenFormat($token) {
        return (bool)preg_match('/^[a-f0-9]{64}$/', $token);
    }

    private function findResetUserByEmail($email) {
        $admin = $this->adminModel->findByEmail($email);
        if ($admin) {
            return [
                'user_type' => 'admin',
                'user_id'   => $admin['admin_id'],
                'name'      => $admin['name'],
                'email'     => $admin['email']
            ];
        }

        $mayor = $this->mayorModel->findByEmail($email);
        if ($mayor) {
            return [
                'user_type' => 'mayor',
                'user_id'   => $mayor['mayor_id'],
                'name'      => $mayor['name'],
                'email'     => $mayor['email']
            ];
        }

        $student = $this->studentModel->findActiveByEmail($email);
        if ($student) {
            return [
                'user_type' => 'student',
                'user_id'   => $student['student_id'],
                'name'      => trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
                'email'     => $student['email']
            ];
        }

        return null;
    }

    private function updateUserPasswordHash($userType, $userId, $passwordHash) {
        if ($userType === 'admin') {
            return $this->adminModel->updatePasswordHash($userId, $passwordHash);
        }
        if ($userType === 'mayor') {
            return $this->mayorModel->updatePasswordHash($userId, $passwordHash);
        }
        if ($userType === 'student') {
            return $this->studentModel->updatePasswordHash($userId, $passwordHash);
        }
        return false;
    }

    private function buildAbsoluteUrl($path) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . baseUrl($path);
    }
}