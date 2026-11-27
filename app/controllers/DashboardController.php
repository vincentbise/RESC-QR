<?php

class DashboardController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $adminModel  = $this->model('Admin');
        $classModel  = $this->model('ClassModel');

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "SELECT e.event_id, e.event_type, e.event_datetime, e.status,
                    COALESCE(v.safe_count, 0) as safe_count,
                    COALESCE(v.missing_count, 0) as missing_count,
                    COALESCE(v.not_in_class_count, 0) as not_in_class_count,
                    COALESCE(v.total_count, 0) as total_count
             FROM emergency_event e
             LEFT JOIN vw_event_status_summary v ON e.event_id = v.event_id
             WHERE e.status = 'Active'
             ORDER BY e.event_datetime DESC LIMIT 1"
        );
        $stmt->execute();
        $activeEvent = $stmt->fetch();

        $stmt2 = $db->prepare(
            "SELECT q.scan_time, q.scan_result, s.first_name, s.last_name, c.section_name
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             ORDER BY q.scan_time DESC LIMIT 10"
        );
        $stmt2->execute();
        $recentScans = $stmt2->fetchAll();

        $outcome = $this->eventOutcome($db);

        $data = [
            'pageTitle'     => 'Dashboard',
            'totalStudents' => $adminModel->getTotalStudents(),
            'activeEvents'  => $adminModel->getActiveEvents(),
            'safeCount'     => $outcome['safeCount'],
            'missingCount'  => $outcome['missingCount'],
            'outcomeNote'   => $outcome['note'],
            'activeEvent'   => $activeEvent,
            'recentScans'   => $recentScans,
            'classes'       => $classModel->getClassesWithCounts(),
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('layouts/footer', $data);
    }

    public function stats() {
        $this->requireAuth(['admin']);
        $adminModel = $this->model('Admin');

        $db = Database::getInstance()->getConnection();

        $outcome = $this->eventOutcome($db);

        $stmt2 = $db->prepare(
            "SELECT q.scan_time, q.scan_result, s.first_name, s.last_name, c.section_name
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             ORDER BY q.scan_time DESC LIMIT 10"
        );
        $stmt2->execute();
        $recentScans = $stmt2->fetchAll();

        $this->json([
            'success'       => true,
            'totalStudents' => (int)$adminModel->getTotalStudents(),
            'safeCount'     => $outcome['safeCount'],
            'missingCount'  => $outcome['missingCount'],
            'outcomeNote'   => $outcome['note'],
            'activeEvents'  => (int)$adminModel->getActiveEvents(),
            'recentScans'   => $recentScans,
        ]);
    }

    /**
     * Safe / Missing totals are only final once an event is closed.
     * - Event active  → counts are null (shown as "—") until it closes.
     * - Otherwise     → counts from the most recently closed event.
     */
    private function eventOutcome($db) {
        $active = (int)$db->query("SELECT COUNT(*) FROM emergency_event WHERE status = 'Active'")->fetchColumn();
        if ($active > 0) {
            return ['safeCount' => null, 'missingCount' => null, 'note' => 'Shown when the event is closed'];
        }

        $last = $db->query(
            "SELECT e.event_type, e.event_datetime,
                    COALESCE(v.safe_count, 0) AS safe_count,
                    COALESCE(v.missing_count, 0) AS missing_count
             FROM emergency_event e
             LEFT JOIN vw_event_status_summary v ON v.event_id = e.event_id
             WHERE e.status = 'Closed'
             ORDER BY e.event_datetime DESC LIMIT 1"
        )->fetch();

        if (!$last) {
            return ['safeCount' => 0, 'missingCount' => 0, 'note' => 'No events yet'];
        }

        return [
            'safeCount'    => (int)$last['safe_count'],
            'missingCount' => (int)$last['missing_count'],
            'note'         => 'Last event: ' . $last['event_type'] . ', ' . date('M d', strtotime($last['event_datetime'])),
        ];
    }
}