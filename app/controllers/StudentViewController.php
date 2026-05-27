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
        $stmt = $db->prepare("SELECT s.*, c.section_name FROM student s JOIN class c ON s.class_id = c.class_id WHERE s.student_id = :id");
        $stmt->execute([':id' => getUserId()]);
        $student = $stmt->fetch();

        $data = ['pageTitle' => 'My Profile', 'student' => $student];
        $this->view('layouts/header', $data);
        $this->view('student/profile', $data);
        $this->view('layouts/footer', $data);
    }
}