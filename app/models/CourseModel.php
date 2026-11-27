<?php

class CourseModel extends Model {

    public function getAll() {
        return $this->findAll('course', 'course_code ASC, course_major ASC');
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
             ORDER BY co.course_code ASC, co.course_major ASC"
        );
        return $stmt->fetchAll();
    }

    public function existsByCodeAndMajor($code, $major) {
        $stmt = $this->query(
            "SELECT course_id FROM course
             WHERE LOWER(course_code) = LOWER(:code) AND LOWER(course_major) = LOWER(:major)
             LIMIT 1",
            [':code' => $code, ':major' => $major]
        );
        return (bool) $stmt->fetch();
    }

    public function createCourse($code, $major) {
        // course_name stays as a readable label (used as class.program and in student forms)
        $name = $major !== '' ? $code . ' - ' . $major : $code;
        return $this->insert('course', [
            'course_code'  => $code,
            'course_major' => $major,
            'course_name'  => $name,
        ]);
    }

    public function deleteCourse($id) {
        return $this->delete('course', 'course_id', $id);
    }
}
