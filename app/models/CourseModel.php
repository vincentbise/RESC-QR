<?php

class CourseModel extends Model {

    public function getAll() {
        return $this->findAll('course', 'course_name ASC');
    }

    public function findByIdCourse($id) {
        return $this->findById('course', 'course_id', $id);
    }

    public function getAllWithCounts() {
        $stmt = $this->query(
            "SELECT co.*,
                    COUNT(DISTINCT c.class_id) AS class_count,
                    COUNT(DISTINCT CASE WHEN s.profile_status = 'Active' THEN s.student_id END) AS student_count
             FROM course co
             LEFT JOIN class c ON c.course_id = co.course_id
             LEFT JOIN student s ON s.class_id = c.class_id
             GROUP BY co.course_id
             ORDER BY co.course_name ASC"
        );
        return $stmt->fetchAll();
    }

    public function existsByCodeOrName($code, $name, $excludeId = null) {
        $sql = "SELECT course_id FROM course WHERE (LOWER(course_code) = LOWER(:code) OR LOWER(course_name) = LOWER(:name))";
        $params = [':code' => $code, ':name' => $name];
        if ($excludeId !== null) {
            $sql .= " AND course_id != :id";
            $params[':id'] = $excludeId;
        }
        $stmt = $this->query($sql . " LIMIT 1", $params);
        return (bool) $stmt->fetch();
    }

    public function createCourse($code, $name) {
        return $this->insert('course', [
            'course_code' => $code,
            'course_name' => $name,
        ]);
    }

    public function deleteCourse($id) {
        return $this->delete('course', 'course_id', $id);
    }
}
