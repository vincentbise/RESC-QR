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

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }

        $rawEmail = $_POST['email'] ?? '';
        $email    = InputValidator::validateEmail($rawEmail);
        $password = $_POST['password'] ?? '';

        // Check lockout FIRST — applies to all failed attempts including bad format
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

        // Invalid email format (e.g. missing @) — counts as a failed attempt
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
        $emailExists  = false;   // tracks whether the email was found at all

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

            // Specific field hint
            if (!$emailExists) {
                // Email not found — highlight email field, also highlight password
                $field    = 'both';
                $fieldMsg = 'Invalid email or password.';
            } else {
                // Email found but wrong password
                $field    = 'password';
                $fieldMsg = 'Incorrect password. Please try again.';
            }

            // Always include attempts-remaining in the general notice
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