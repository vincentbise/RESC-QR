<?php

class StudentViewController extends Controller {

    public function __construct() {
        $this->requireAuth(['student']);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT s.*, c.section_name, c.program FROM student s JOIN class c ON s.class_id = c.class_id WHERE s.student_id = :id");
        $stmt->execute([':id' => getUserId()]);
        $student = $stmt->fetch();

        $stmt2 = $db->prepare(
            "SELECT ss.status, e.event_type, e.event_datetime
             FROM student_status ss
             JOIN emergency_event e ON ss.event_id = e.event_id
             WHERE ss.student_id = :sid AND e.status = 'Active'
             ORDER BY e.event_datetime DESC LIMIT 1"
        );
        $stmt2->execute([':sid' => getUserId()]);
        $status = $stmt2->fetch();

        if (!$status) {
            $status = [
                'status'         => 'Safe',
                'event_type'     => null,
                'event_datetime' => null,
                'default'        => true,
            ];
        }

        $data = ['pageTitle' => 'My Dashboard', 'student' => $student, 'status' => $status];
        $this->view('layouts/header', $data);
        $this->view('student/dashboard', $data);
        $this->view('layouts/footer', $data);
    }

    public function qrcode() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT qr_code_value, first_name, last_name FROM student WHERE student_id = :id");
        $stmt->execute([':id' => getUserId()]);
        $student = $stmt->fetch();

        $data = ['pageTitle' => 'My QR Code', 'student' => $student];
        $this->view('layouts/header', $data);
        $this->view('student/qr_code', $data);
        $this->view('layouts/footer', $data);
    }

    public function profile() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT s.*, c.section_name, c.program FROM student s JOIN class c ON s.class_id = c.class_id WHERE s.student_id = :id");
        $stmt->execute([':id' => getUserId()]);
        $student = $stmt->fetch();

        require_once ROOT_PATH . '/app/models/ClassModel.php';
        $classModel = new ClassModel();
        $classes = $classModel->getAll();

        $data = ['pageTitle' => 'My Profile', 'student' => $student, 'classes' => $classes];
        $this->view('layouts/header', $data);
        $this->view('student/profile', $data);
        $this->view('layouts/footer', $data);
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/studentview/profile');
            return;
        }

        $this->validateCSRF();

        $id = getUserId();

        $data = [];
        if (isset($_POST['first_name'])) $data['first_name'] = InputValidator::validateName($_POST['first_name']);
        if (isset($_POST['last_name']))  $data['last_name']  = InputValidator::validateName($_POST['last_name']);
        if (isset($_POST['email']))      $data['email']      = InputValidator::validateEmail($_POST['email']) ?: null;
        if (isset($_POST['phone']))      $data['phone']      = InputValidator::sanitizeString($_POST['phone']);
        if (isset($_POST['class_id']))   $data['class_id']   = InputValidator::validateId($_POST['class_id']);

        if (empty($data['first_name']) || empty($data['last_name'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'First name and last name are required.'], 422);
                return;
            }
            $this->setFlash('error', 'First name and last name are required.');
            $this->redirect('/studentview/profile');
            return;
        }

        $data = array_filter($data, fn($v) => $v !== false && $v !== null);

        require_once ROOT_PATH . '/app/models/Student.php';
        $studentModel = new Student();
        $studentModel->updateStudent($id, $data);

        if ($this->isAjax()) {
            $student = $studentModel->findStudentById($id);
            $this->json(['success' => true, 'message' => 'Profile updated successfully.', 'student' => $student]);
            return;
        }

        $this->setFlash('success', 'Profile updated successfully.');
        $this->redirect('/studentview/profile');
    }
}