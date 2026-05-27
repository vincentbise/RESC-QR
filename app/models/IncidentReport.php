<?php

class IncidentReport extends Model {

    public function getAll() {
        $stmt = $this->query(
            "SELECT r.*, e.event_type, e.event_datetime, a.name as generated_by_name
             FROM incident_report r
             JOIN emergency_event e ON r.event_id = e.event_id
             LEFT JOIN admin a ON r.generated_by = a.admin_id
             ORDER BY r.report_time DESC"
        );
        return $stmt->fetchAll();
    }

    public function existsForEvent($eventId) {
        $stmt = $this->query(
            "SELECT COUNT(*) as total FROM incident_report WHERE event_id = :eid",
            [':eid' => $eventId]
        );
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function generate($eventId, $adminId, $summary) {
        if ($this->existsForEvent($eventId)) {
            throw new Exception('A report for this event has already been generated.');
        }

        require_once ROOT_PATH . '/app/models/StudentStatus.php';
        $statusModel = new StudentStatus();
        $counts = $statusModel->getSummary($eventId);

        $safeCount       = isset($counts['safe_count'])           ? (int)$counts['safe_count']           : 0;
        $missingCount    = isset($counts['missing_count'])        ? (int)$counts['missing_count']        : 0;
        $notInClassCount = isset($counts['not_in_class_count'])   ? (int)$counts['not_in_class_count']   : 0;
        $total           = isset($counts['total'])                ? (int)$counts['total']                : 0;

        return $this->insert('incident_report', [
            'event_id'           => $eventId,
            'generated_by'       => $adminId,
            'report_time'        => date('Y-m-d H:i:s'),
            'summary_text'       => $summary,
            'total_students'     => $total,
            'safe_count'         => $safeCount,
            'missing_count'      => $missingCount,
            'not_in_class_count' => $notInClassCount,
        ]);
    }

    public function findReport($id) {
        $stmt = $this->query(
            "SELECT r.*, e.event_type, e.event_datetime, e.description as event_description,
                    a.name as generated_by_name
             FROM incident_report r
             JOIN emergency_event e ON r.event_id = e.event_id
             LEFT JOIN admin a ON r.generated_by = a.admin_id
             WHERE r.report_id = :id",
            [':id' => $id]
        );
        return $stmt->fetch();
    }
}