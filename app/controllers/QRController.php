<?php

class QRController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');

        $classId = InputValidator::validateId($_GET['class_id'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 12;
        $total = (int)$studentModel->countFiltered('', $classId ?: null);
        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $students = $studentModel->getAllPaginated('', $classId ?: null, $perPage, $offset);
        $classes = $classModel->getAll();

        if ($this->isAjax()) {
            $qrStudents = array_map(function($s) {
                $s['qr_url'] = generateQRCodeUrl($s['qr_code_value'], 200);
                return $s;
            }, $students);
            $this->json([
                'success'     => true,
                'students'    => $qrStudents,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
            ]);
            return;
        }

        $data = [
            'pageTitle' => 'QR Codes',
            'students'  => $students,
            'classes'   => $classes,
            'classId'   => $classId,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/qr_codes', $data);
        $this->view('layouts/footer', $data);
    }
}
