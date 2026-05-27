<?php

class QRScanLog extends Model {

    public function logScan($studentId, $eventId, $scannedBy, $result = 'Valid') {
        return $this->insert('qr_scan_log', [
            'student_id'  => $studentId,
            'event_id'    => $eventId,
            'scanned_by'  => $scannedBy,
            'scan_time'   => date('Y-m-d H:i:s'),
            'scan_result' => $result
        ]);
    }

    public function getLogsByEvent($eventId) {
        $stmt = $this->query(
            "SELECT q.*, s.first_name, s.last_name, s.qr_code_value, c.section_name, m.name as mayor_name
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             JOIN class_mayor m ON q.scanned_by = m.mayor_id
             WHERE q.event_id = :eid ORDER BY q.scan_time DESC",
            [':eid' => $eventId]
        );
        return $stmt->fetchAll();
    }

    public function getAllLogs($limit = 100) {
        $stmt = $this->query(
            "SELECT q.*, s.first_name, s.last_name, c.section_name, m.name as mayor_name,
                    e.event_type, e.event_datetime
             FROM qr_scan_log q
             JOIN student s ON q.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             JOIN class_mayor m ON q.scanned_by = m.mayor_id
             JOIN emergency_event e ON q.event_id = e.event_id
             ORDER BY q.scan_time DESC LIMIT " . (int)$limit
        );
        return $stmt->fetchAll();
    }
}