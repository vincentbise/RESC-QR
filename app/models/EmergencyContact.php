<?php

class EmergencyContact extends Model {

    public function getByStudent($studentId) {
        $stmt = $this->query(
            "SELECT * FROM emergency_contact WHERE student_id = :sid ORDER BY contact_name",
            [':sid' => $studentId]
        );
        return $stmt->fetchAll();
    }

    public function addContact($data) {
        return $this->insert('emergency_contact', $data);
    }

    public function updateContact($id, $data) {
        return $this->update('emergency_contact', $data, 'contact_id', $id);
    }

    public function deleteContact($id) {
        return $this->delete('emergency_contact', 'contact_id', $id);
    }
}
