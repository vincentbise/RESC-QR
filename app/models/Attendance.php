<?php

class Attendance extends Model {

    public function getByClassAndDate($classId, $date) {
        $stmt = $this->query(
            "SELECT a.*, s.first_name, s.last_name, s.qr_code_value
             FROM attendance a
             JOIN student s ON a.student_id = s.student_id
             WHERE a.class_id = :cid AND a.date = :date
             ORDER BY s.last_name ASC",
            [':cid' => $classId, ':date' => $date]
        );
        return $stmt->fetchAll();
    }

    public function markAttendance($studentId, $classId, $date, $status, $recordedBy = null) {
        $existing = $this->query(
            "SELECT attendance_id FROM attendance WHERE student_id = :sid AND date = :date",
            [':sid' => $studentId, ':date' => $date]
        )->fetch();

        if ($existing) {
            return $this->update('attendance', ['status' => $status], 'attendance_id', $existing['attendance_id']);
        }

        return $this->insert('attendance', [
            'student_id'  => $studentId,
            'class_id'    => $classId,
            'date'        => $date,
            'status'      => $status,
            'recorded_by' => $recordedBy,
        ]);
    }

    public function getStatusMapByClassAndDate($classId, $date) {
        $sql = "SELECT a.student_id, a.status
                FROM attendance a
                JOIN student s ON a.student_id = s.student_id
                WHERE a.date = :date";
        $params = [':date' => $date];

        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $stmt = $this->query($sql, $params);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['student_id']] = $row['status'];
        }
        return $map;
    }
}