<?php

class ClassModel extends Model {

    public function getAll() {
        return $this->findAll('class', 'program ASC, year_level ASC, section_name ASC');
    }

    public function findClassById($id) {
        return $this->findById('class', 'class_id', $id);
    }

    public function createClass($data) {
        return $this->insert('class', $data);
    }

    public function updateClass($id, $data) {
        return $this->update('class', $data, 'class_id', $id);
    }

    public function getClassesWithCounts() {
        $stmt = $this->query(
            "SELECT c.*, COUNT(s.student_id) as student_count
             FROM class c
             LEFT JOIN student s ON c.class_id = s.class_id AND s.profile_status = 'Active'
             GROUP BY c.class_id
             ORDER BY c.program ASC, c.year_level ASC"
        );
        return $stmt->fetchAll();
    }
}