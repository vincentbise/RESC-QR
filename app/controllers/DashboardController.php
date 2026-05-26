<?php

class DashboardController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $adminModel  = $this->model('Admin');
        $classModel  = $this->model('ClassModel');

        $db = Database::getInstance()->getConnection();

        // Active event with status counts
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

        // Recent scans
        $stmt2 = $db->prepare(
            "SELECT q.scan_time, q.scan_result, s.first_name, s.last_name, c.section_name
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             ORDER BY q.scan_time DESC LIMIT 10"
        );
        $stmt2->execute();
        $recentScans = $stmt2->fetchAll();

        $data = [
            'pageTitle'     => 'Dashboard',
            'totalStudents' => $adminModel->getTotalStudents(),
            'activeEvents'  => $adminModel->getActiveEvents(),
            'safeCount'     => $activeEvent ? (int)$activeEvent['safe_count'] : 0,
            'missingCount'  => $activeEvent ? (int)$activeEvent['missing_count'] : 0,
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

        // Live counts from active event
        $stmt = $db->prepare(
            "SELECT
                COALESCE(SUM(ss.status='Safe'), 0) AS safe_count,
                COALESCE(SUM(ss.status='Missing'), 0) AS missing_count,
                COALESCE(SUM(ss.status='Not in class'), 0) AS not_in_class_count
             FROM student_status ss
             JOIN emergency_event e ON ss.event_id = e.event_id
             WHERE e.status = 'Active'"
        );
        $stmt->execute();
        $counts = $stmt->fetch();

        // Recent scans for live update
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
            'safeCount'     => (int)($counts['safe_count'] ?? 0),
            'missingCount'  => (int)($counts['missing_count'] ?? 0),
            'notInClass'    => (int)($counts['not_in_class_count'] ?? 0),
            'activeEvents'  => (int)$adminModel->getActiveEvents(),
            'recentScans'   => $recentScans,
        ]);
    }
}