<?php

class ReportController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $reportModel = $this->model('IncidentReport');
        $eventModel  = $this->model('EmergencyEvent');
        $reports     = $reportModel->getAll();

        if ($this->isAjax()) {
            $this->json(['success' => true, 'reports' => $reports]);
            return;
        }

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/report');
            return;
        }

        // Read JSON body sent by App.post()
        $input   = $this->getJsonInput();
        $eventId = InputValidator::validateId($input['event_id'] ?? '');
        $summary = InputValidator::sanitizeString($input['summary'] ?? '');

        if (!$eventId) {
            $this->json(['success' => false, 'message' => 'Please select a valid event.'], 400);
            return;
        }

        try {
            $reportModel = $this->model('IncidentReport');
            $id = $reportModel->generate($eventId, getUserId(), $summary ?: 'Auto-generated incident report.');

            if (!$id) {
                $this->json(['success' => false, 'message' => 'Insert failed — no ID returned.'], 500);
                return;
            }

            $report = $reportModel->findReport($id);
            $this->json([
                'success' => true,
                'message' => 'Report generated successfully.',
                'report'  => $report,
            ]);

        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
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