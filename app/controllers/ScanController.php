<?php

class ScanController extends Controller {

    private $studentModel;
    private $scanLogModel;
    private $statusModel;
    private $eventModel;

    public function __construct() {
        $this->requireAuth(['mayor', 'admin']);
        $this->studentModel = $this->model('Student');
        $this->scanLogModel = $this->model('QRScanLog');
        $this->statusModel  = $this->model('StudentStatus');
        $this->eventModel   = $this->model('EmergencyEvent');
    }

    public function index() {
        $activeEvent = $this->eventModel->getActiveEvent();
        $data = [
            'pageTitle'   => 'QR Scanner',
            'activeEvent' => $activeEvent,
            'classId'     => $_SESSION['class_id'] ?? null,
            'extraJs'     => ['scanner.js'],
            'extraCss'    => ['scanner.css'],
        ];

        $this->view('layouts/header', $data);
        $this->view('mayor/scanner', $data);
        $this->view('layouts/footer', $data);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }

        $qrCode = InputValidator::sanitizeString($_POST['qr_code'] ?? '');
        if (!$qrCode) {
            $this->json(['success' => false, 'message' => 'No QR code data received.']);
            return;
        }

        $activeEvent = $this->eventModel->getActiveEvent();
        if (!$activeEvent) {
            $this->json(['success' => false, 'message' => 'No active emergency event.']);
            return;
        }

        $student = $this->studentModel->findByQRCode($qrCode);
        if (!$student) {
            $this->scanLogModel->logScan(0, $activeEvent['event_id'], getUserId(), 'Invalid');
            $this->json(['success' => false, 'message' => 'Student not found for this QR code.', 'scan_result' => 'Invalid']);
            return;
        }

        $this->scanLogModel->logScan($student['student_id'], $activeEvent['event_id'], getUserId(), 'Valid');
        $this->statusModel->setStatus($student['student_id'], $activeEvent['event_id'], 'Safe');

        $this->json([
            'success'     => true,
            'message'     => $student['first_name'] . ' ' . $student['last_name'] . ' marked as SAFE',
            'scan_result' => 'Valid',
            'student'     => [
                'id'         => $student['student_id'],
                'name'       => $student['first_name'] . ' ' . $student['last_name'],
                'section'    => $student['section_name'],
                'qr_code'    => $student['qr_code_value'],
            ]
        ]);
    }

    public function students() {
        $activeEvent = $this->eventModel->getActiveEvent();
        $classId = $_SESSION['class_id'] ?? null;

        $students = [];
        $summary = ['safe_count' => 0, 'missing_count' => 0, 'not_in_class_count' => 0, 'total' => 0];

        if ($activeEvent) {
            $statuses = $this->statusModel->getStatusesByEvent($activeEvent['event_id']);
            if ($classId) {
                $statuses = array_filter($statuses, fn($s) => true);
            }
            $students = $statuses;
            $summary = $this->statusModel->getSummary($activeEvent['event_id']);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'students' => array_values($students), 'summary' => $summary]);
            return;
        }

        $data = [
            'pageTitle'   => 'Student Status',
            'students'    => $students,
            'summary'     => $summary,
            'activeEvent' => $activeEvent,
            'extraJs'     => ['dashboard.js'],
        ];

        $this->view('layouts/header', $data);
        $this->view('mayor/student_list', $data);
        $this->view('layouts/footer', $data);
    }

    public function hotlines() {
        $data = [
            'pageTitle' => 'Emergency Hotlines',
            'hotlines'  => EMERGENCY_HOTLINES,
        ];
        $this->view('layouts/header', $data);
        $this->view('mayor/hotlines', $data);
        $this->view('layouts/footer', $data);
    }
}
