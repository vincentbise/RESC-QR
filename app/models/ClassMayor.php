<?php

class ClassMayor extends Model {

    public function findByEmail($email) {
        $stmt = $this->query(
            "SELECT cm.*, c.section_name, c.program, c.year_level AS class_year
             FROM class_mayor cm
             JOIN class c ON cm.class_id = c.class_id
             WHERE cm.email = :email",
            [':email' => $email]
        );
        return $stmt->fetch();
    }

    public function findByIdMayor($id) {
        return $this->findById('class_mayor', 'mayor_id', $id);
    }

    public function getAll() {
        $stmt = $this->query(
            "SELECT cm.*, c.section_name, c.program
             FROM class_mayor cm
             JOIN class c ON cm.class_id = c.class_id
             ORDER BY cm.name ASC"
        );
        return $stmt->fetchAll();
    }

    public function getStudentsByClass($classId) {
        $stmt = $this->query(
            "SELECT * FROM student WHERE class_id = :class_id AND profile_status = 'Active' ORDER BY last_name ASC",
            [':class_id' => $classId]
        );
        return $stmt->fetchAll();
    }

    public function getClassStudentCount($classId) {
        return $this->count('student', "class_id = :class_id AND profile_status = 'Active'", [':class_id' => $classId]);
    }
}
