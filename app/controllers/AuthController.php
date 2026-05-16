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

        $email    = InputValidator::validateEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please enter valid credentials.']);
                return;
            }
            $this->setFlash('error', 'Please enter valid credentials.');
            $this->redirect('/auth/login');
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        if (!$this->checkLoginAttempts($ip)) {
            $msg = 'Too many failed attempts. Please try again in 15 minutes.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 429);
                return;
            }
            $this->setFlash('error', $msg);
            $this->redirect('/auth/login');
            return;
        }

        $userId = null;
        $userName = null;
        $userRole = null;

        $admin = $this->adminModel->findByEmail($email);
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $userId = $admin['admin_id'];
            $userName = $admin['name'];
            $userRole = 'admin';
        }

        if (!$userId) {
            $mayor = $this->mayorModel->findByEmail($email);
            if ($mayor && password_verify($password, $mayor['password_hash'])) {
                $userId = $mayor['mayor_id'];
                $userName = $mayor['name'];
                $userRole = 'mayor';
                $_SESSION['class_id'] = $mayor['class_id'];
                $_SESSION['section_name'] = $mayor['section_name'];
            }
        }

        if (!$userId) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM student WHERE email = :email AND profile_status = 'Active'");
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch();
            if ($student && password_verify($password, $student['password_hash'] ?? '')) {
                $userId = $student['student_id'];
                $userName = $student['first_name'] . ' ' . $student['last_name'];
                $userRole = 'student';
            }
        }

        if ($userId) {
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
            $this->logFailedAttempt($ip, $email);
            $msg = 'Invalid email or password.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg]);
                return;
            }
            $this->setFlash('error', $msg);
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
            "SELECT COUNT(*) as attempts FROM login_attempts
             WHERE ip_address = :ip AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stmt->execute([':ip' => $ip]);
        $result = $stmt->fetch();
        return ($result['attempts'] < 5);
    }

    private function logFailedAttempt($ip, $email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (:ip, :email, NOW())"
        );
        $stmt->execute([':ip' => $ip, ':email' => $email]);
    }
}
