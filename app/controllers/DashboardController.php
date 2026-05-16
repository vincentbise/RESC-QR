<?php

class DashboardController extends Controller {

    public function __construct() {
        $this->requireAuth(['admin']);
    }

    public function index() {
        $adminModel = $this->model('Admin');
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');

        $data = [
            'pageTitle'     => 'Dashboard',
            'totalStudents' => $adminModel->getTotalStudents(),
            'totalClasses'  => $adminModel->getTotalClasses(),
            'totalMayors'   => $adminModel->getTotalMayors(),
            'activeEvents'  => $adminModel->getActiveEvents(),
            'safeCount'     => 0,
            'missingCount'  => 0,
            'classes'       => $classModel->getClassesWithCounts(),
        ];

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

        if ($activeEvent) {
            $data['activeEvent']  = $activeEvent;
            $data['safeCount']    = $activeEvent['safe_count'];
            $data['missingCount'] = $activeEvent['missing_count'];
        }

        $stmt = $db->prepare(
            "SELECT q.scan_time, s.first_name, s.last_name, c.section_name, q.scan_result
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             ORDER BY q.scan_time DESC LIMIT 10"
        );
        $stmt->execute();
        $data['recentScans'] = $stmt->fetchAll();

        if ($this->isAjax()) {
            $this->json([
                'success'       => true,
                'totalStudents' => $data['totalStudents'],
                'safeCount'     => $data['safeCount'],
                'missingCount'  => $data['missingCount'],
                'activeEvents'  => $data['activeEvents'],
                'recentScans'   => $data['recentScans'],
            ]);
            return;
        }

        $this->view('layouts/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('layouts/footer', $data);
    }

    public function stats() {
        $this->requireAuth(['admin']);
        $adminModel = $this->model('Admin');

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(status='Safe'),0) as safe,
                    COALESCE(SUM(status='Missing'),0) as missing,
                    COALESCE(SUM(status='Not in class'),0) as not_in_class
             FROM student_status ss
             JOIN emergency_event e ON ss.event_id = e.event_id
             WHERE e.status = 'Active'"
        );
        $stmt->execute();
        $counts = $stmt->fetch();

        $this->json([
            'success'       => true,
            'totalStudents' => $adminModel->getTotalStudents(),
            'safeCount'     => (int)$counts['safe'],
            'missingCount'  => (int)$counts['missing'],
            'notInClass'    => (int)$counts['not_in_class'],
            'activeEvents'  => $adminModel->getActiveEvents(),
        ]);
    }
}
