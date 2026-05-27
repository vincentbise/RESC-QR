<?php

class Admin extends Model {

    public function findByEmail($email) {
        $stmt = $this->query("SELECT * FROM admin WHERE email = :email", [':email' => $email]);
        return $stmt->fetch();
    }

    public function findById_admin($id) {
        return $this->findById('admin', 'admin_id', $id);
    }

    public function getAll() {
        return $this->findAll('admin', 'name ASC');
    }

    public function getTotalStudents() {
        return $this->count('student', "profile_status = 'Active'");
    }

    public function getTotalClasses() {
        return $this->count('class');
    }

    public function getTotalMayors() {
        return $this->count('class_mayor');
    }

    public function getActiveEvents() {
        return $this->count('emergency_event', "status = 'Active'");
    }
}