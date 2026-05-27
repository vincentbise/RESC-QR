<?php

class EmergencyEvent extends Model {

    public function getAll() {
        $stmt = $this->query(
            "SELECT e.*, a.name as created_by_name,
                    COALESCE(v.safe_count, 0) as safe_count,
                    COALESCE(v.missing_count, 0) as missing_count
             FROM emergency_event e
             JOIN admin a ON e.created_by = a.admin_id
             LEFT JOIN vw_event_status_summary v ON e.event_id = v.event_id
             ORDER BY e.event_datetime DESC"
        );
        return $stmt->fetchAll();
    }

    public function findEvent($id) {
        $stmt = $this->query(
            "SELECT e.*, a.name as created_by_name
             FROM emergency_event e
             JOIN admin a ON e.created_by = a.admin_id
             WHERE e.event_id = :id",
            [':id' => $id]
        );
        return $stmt->fetch();
    }

    public function createEvent($data) {
        return $this->insert('emergency_event', $data);
    }

    public function activate($id) {
        return $this->update('emergency_event', ['status' => 'Active'], 'event_id', $id);
    }

    public function close($id) {
        return $this->update('emergency_event', ['status' => 'Closed'], 'event_id', $id);
    }

    public function getActiveEvent() {
        $stmt = $this->query("SELECT * FROM emergency_event WHERE status = 'Active' ORDER BY event_datetime DESC LIMIT 1");
        return $stmt->fetch();
    }

    public function initializeStatuses($eventId) {
        $this->query(
            "INSERT IGNORE INTO student_status (student_id, event_id, status)
             SELECT s.student_id, e.event_id,
                      CASE WHEN a.status = 'Absent' THEN 'Absent' ELSE 'Not Yet Scanned' END
             FROM student s
             JOIN emergency_event e ON e.event_id = :eid
             LEFT JOIN attendance a
                ON a.student_id = s.student_id
               AND a.date = DATE(e.event_datetime)
             WHERE s.profile_status = 'Active'",
            [':eid' => $eventId]
        );
    }
}