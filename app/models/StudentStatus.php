<?php

class StudentStatus extends Model {

    public function setStatus($studentId, $eventId, $status) {
        $stmt = $this->query(
            "INSERT INTO student_status (student_id, event_id, status, updated_at)
             VALUES (:sid, :eid, :status, NOW())
             ON DUPLICATE KEY UPDATE status = :status2, updated_at = NOW()",
            [':sid' => $studentId, ':eid' => $eventId, ':status' => $status, ':status2' => $status]
        );
        return $stmt;
    }

    public function getStudentStatus($studentId, $eventId) {
        $stmt = $this->query(
            "SELECT * FROM student_status WHERE student_id = :sid AND event_id = :eid LIMIT 1",
            [':sid' => $studentId, ':eid' => $eventId]
        );
        return $stmt->fetch();
    }

    public function getStatusesByEvent($eventId, $classId = null) {
        $sql = "SELECT ss.*, s.first_name, s.last_name, s.phone, s.email, s.qr_code_value,
                       s.class_id, c.section_name, c.program
                FROM student_status ss
                JOIN student s ON ss.student_id = s.student_id
                JOIN class c ON s.class_id = c.class_id
                WHERE ss.event_id = :eid";

        $params = [':eid' => $eventId];

        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql .= " ORDER BY ss.status ASC, s.last_name ASC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getMissingByEvent($eventId) {
        $stmt = $this->query(
            "SELECT s.*, c.section_name FROM student_status ss
             JOIN student s ON ss.student_id = s.student_id
             JOIN class c ON s.class_id = c.class_id
             WHERE ss.event_id = :eid AND ss.status = 'Not Yet Scanned' ORDER BY s.last_name",
            [':eid' => $eventId]
        );
        return $stmt->fetchAll();
    }

    public function getSummary($eventId, $classId = null) {
        $sql = "SELECT
                    COALESCE(SUM(ss.status='Safe'),0) as safe_count,
                    COALESCE(SUM(ss.status='Not Yet Scanned'),0) as missing_count,
                    COALESCE(SUM(ss.status='Not in class'),0) as not_in_class_count,
                    COUNT(*) as total
                FROM student_status ss
                JOIN student s ON ss.student_id = s.student_id
                WHERE ss.event_id = :eid";

        $params = [':eid' => $eventId];

        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
}