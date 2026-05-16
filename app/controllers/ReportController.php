<?php

class ReportController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $reportModel = $this->model('IncidentReport');
        $reports = $reportModel->getAll();

        if ($this->isAjax()) {
            $this->json(['success' => true, 'reports' => $reports]);
            return;
        }

        $eventModel = $this->model('EmergencyEvent');
        $data = [
            'pageTitle' => 'Reports',
            'reports'   => $reports,
            'events'    => $eventModel->getAll(),
        ];
        $this->view('layouts/header', $data);
        $this->view('admin/reports', $data);
        $this->view('layouts/footer', $data);
    }

    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/report'); return; }

        $eventId = InputValidator::validateId($_POST['event_id'] ?? '');
        $summary = InputValidator::sanitizeString($_POST['summary'] ?? '');

        if (!$eventId) {
            if ($this->isAjax()) { $this->json(['success' => false, 'message' => 'Invalid event.'], 400); return; }
            $this->redirect('/report');
            return;
        }

        $reportModel = $this->model('IncidentReport');
        $id = $reportModel->generate($eventId, getUserId(), $summary ?: 'Auto-generated incident report');

        if ($this->isAjax()) {
            $report = $reportModel->findReport($id);
            $this->json(['success' => true, 'message' => 'Report generated successfully.', 'report' => $report]);
            return;
        }

        $this->setFlash('success', 'Report generated.');
        $this->redirect('/report');
    }

    public function scanlogs() {
        $scanModel = $this->model('QRScanLog');
        $logs = $scanModel->getAllLogs(200);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'logs' => $logs]);
            return;
        }

        $data = ['pageTitle' => 'Scan Logs', 'logs' => $logs];
        $this->view('layouts/header', $data);
        $this->view('admin/scan_logs', $data);
        $this->view('layouts/footer', $data);
    }
}
