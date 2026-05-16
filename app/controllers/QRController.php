<?php

class QRController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');

        $classId = InputValidator::validateId($_GET['class_id'] ?? '');
        $students = $studentModel->getAll('', $classId ?: null);
        $classes = $classModel->getAll();

        if ($this->isAjax()) {
            $qrStudents = array_map(function($s) {
                $s['qr_url'] = generateQRCodeUrl($s['qr_code_value'], 200);
                return $s;
            }, $students);
            $this->json(['success' => true, 'students' => $qrStudents]);
            return;
        }

        $data = [
            'pageTitle' => 'QR Codes',
            'students'  => $students,
            'classes'   => $classes,
            'classId'   => $classId,
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/qr_codes', $data);
        $this->view('layouts/footer', $data);
    }
}
