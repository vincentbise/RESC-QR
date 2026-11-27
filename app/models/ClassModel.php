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

    public function deleteClass($id) {
        return $this->delete('class', 'class_id', $id);
    }

    public function sectionExists($sectionName, $courseId) {
        $stmt = $this->query(
            "SELECT class_id FROM class WHERE LOWER(section_name) = LOWER(:sec) AND course_id = :cid LIMIT 1",
            [':sec' => $sectionName, ':cid' => $courseId]
        );
        return (bool) $stmt->fetch();
    }

    public function getClassesWithCounts() {
        $stmt = $this->query(
            "SELECT c.*, COUNT(s.student_id) as student_count
             FROM class c
             LEFT JOIN student s ON c.class_id = s.class_id AND s.profile_status = 'Active'
             GROUP BY c.class_id
             ORDER BY c.program ASC, c.year_level ASC, c.section_name ASC"
        );
        return $stmt->fetchAll();
    }

    public function getClassesWithMayorsAndCounts() {
        $stmt = $this->query(
            "SELECT c.*,
                    COALESCE(co.course_code, '') AS course_code,
                    COUNT(DISTINCT CASE WHEN s.profile_status = 'Active' THEN s.student_id END) AS student_count,
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN (cm.mayor_id IS NOT NULL AND COALESCE(up_m.is_mayor, 1) = 1)
                              OR COALESCE(up_s.is_mayor, 0) = 1
                            THEN CONCAT(s.first_name, ' ', s.last_name)
                        END
                        ORDER BY s.last_name ASC SEPARATOR ', '
                    ) AS mayor_names
             FROM class c
             LEFT JOIN course co ON co.course_id = c.course_id
             LEFT JOIN student s ON s.class_id = c.class_id AND s.profile_status = 'Active'
             LEFT JOIN class_mayor cm ON cm.student_id = s.student_id
             LEFT JOIN user_permissions up_m ON up_m.user_type = 'mayor' AND up_m.user_id = cm.mayor_id
             LEFT JOIN user_permissions up_s ON up_s.user_type = 'student' AND up_s.user_id = s.student_id
             GROUP BY c.class_id
             ORDER BY c.program ASC, c.year_level ASC, c.section_name ASC"
        );
        return $stmt->fetchAll();
    }
}
