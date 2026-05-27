<?php

class Student extends Model {

    public function getAll($search = '', $classId = null) {
        $sql = "SELECT s.*, c.section_name, c.program
                FROM student s
                JOIN class c ON s.class_id = c.class_id
                WHERE s.profile_status = 'Active'";
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.email LIKE :search3)";
            $params[':search1'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
        }
        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql .= " ORDER BY s.last_name ASC";
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getAllPaginated($search = '', $classId = null, $limit = 10, $offset = 0) {
        $sql = "SELECT s.*, c.section_name, c.program
                FROM student s
                JOIN class c ON s.class_id = c.class_id
                WHERE s.profile_status = 'Active'";
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.email LIKE :search3)";
            $params[':search1'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
        }
        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql .= " ORDER BY s.last_name ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered($search = '', $classId = null) {
        $sql = "SELECT COUNT(*) as total
                FROM student s
                JOIN class c ON s.class_id = c.class_id
                WHERE s.profile_status = 'Active'";
        $params = [];

        if ($search) {
            $sql .= " AND (s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.email LIKE :search3)";
            $params[':search1'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
        }
        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch()['total'];
    }

    public function findStudentById($id) {
        $stmt = $this->query(
            "SELECT s.*, c.section_name, c.program
             FROM student s JOIN class c ON s.class_id = c.class_id
             WHERE s.student_id = :id",
            [':id' => $id]
        );
        return $stmt->fetch();
    }

    public function create($data) {
        $qrCode = 'RESC-STU-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT) . '-' . time();
        $defaultPassword = password_hash('Student@123', PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->insert('student', [
            'class_id'       => $data['class_id'],
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'email'          => $data['email'] ?? null,
            'password_hash'  => $defaultPassword,
            'phone'          => $data['phone'] ?? null,
            'course'         => $data['course'],
            'year_level'     => $data['year_level'],
            'qr_code_value'  => $qrCode,
            'profile_status' => 'Active'
        ]);
    }

    public function updateStudent($id, $data) {
        return $this->update('student', $data, 'student_id', $id);
    }

    public function deleteStudent($id) {
        return $this->update('student', ['profile_status' => 'Inactive'], 'student_id', $id);
    }

    public function findByQRCode($qrCode) {
        $stmt = $this->query(
            "SELECT s.*, c.section_name FROM student s
             JOIN class c ON s.class_id = c.class_id
             WHERE s.qr_code_value = :qr AND s.profile_status = 'Active'",
            [':qr' => $qrCode]
        );
        return $stmt->fetch();
    }

    public function getStudentCount() {
        return $this->count('student', "profile_status = 'Active'");
    }

    public function getCountByClass($classId) {
        return $this->count('student', "class_id = :cid AND profile_status = 'Active'", [':cid' => $classId]);
    }
}
