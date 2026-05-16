<?php

class IncidentReport extends Model {

    public function getAll() {
        $stmt = $this->query(
            "SELECT r.*, e.event_type, e.event_datetime, a.name as generated_by_name
             FROM incident_report r
             JOIN emergency_event e ON r.event_id = e.event_id
             JOIN admin a ON r.generated_by = a.admin_id
             ORDER BY r.report_time DESC"
        );
        return $stmt->fetchAll();
    }

    public function generate($eventId, $adminId, $summary) {
        $statusModel = new StudentStatus();
        $counts = $statusModel->getSummary($eventId);

        return $this->insert('incident_report', [
            'event_id'           => $eventId,
            'generated_by'       => $adminId,
            'report_time'        => date('Y-m-d H:i:s'),
            'summary_text'       => $summary,
            'total_students'     => $counts['total'],
            'safe_count'         => $counts['safe_count'],
            'missing_count'      => $counts['missing_count'],
            'not_in_class_count' => $counts['not_in_class_count'],
        ]);
    }

    public function findReport($id) {
        $stmt = $this->query(
            "SELECT r.*, e.event_type, e.event_datetime, e.description as event_description, a.name as generated_by_name
             FROM incident_report r
             JOIN emergency_event e ON r.event_id = e.event_id
             JOIN admin a ON r.generated_by = a.admin_id
             WHERE r.report_id = :id",
            [':id' => $id]
        );
        return $stmt->fetch();
    }
}
