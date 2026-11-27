<?php

class UserPermission extends Model {

    // All available permissions with labels
    const PERMISSIONS = [
        'perm_qr_scanner'        => 'QR Scanner',
        'perm_generate_report'   => 'Reports',
        'perm_declare_emergency' => 'Declare Emergency',
        'perm_qr_codes'          => 'QR Codes',
        'perm_student_list_mayor'=> 'Student List',
    ];

    // Default permissions for each role when "is_mayor" is toggled on
    const MAYOR_DEFAULTS = [
        'perm_qr_scanner'        => 1,
        'perm_generate_report'   => 0,
        'perm_declare_emergency' => 0,
        'perm_qr_codes'          => 0,
        'perm_student_list_mayor'=> 1,
    ];

    /**
     * Get all users (admins, mayors, students) with their permissions
     */
    public function getAllUsersWithPermissions() {
        $sql = "SELECT 
                    'admin' AS user_type, 
                    a.admin_id AS user_id, 
                    a.name, 
                    a.name AS sort_name,
                    a.email, 
                    a.role AS original_role,
                    NULL AS section_name,
                    NULL AS program,
                    a.created_at,
                    p.permission_id,
                    p.is_mayor,
                    p.perm_qr_scanner,
                    p.perm_generate_report,
                    p.perm_declare_emergency,
                    p.perm_qr_codes,
                    p.perm_emergency_hotlines,
                    p.perm_my_qr,
                    p.perm_student_list_admin,
                    p.perm_student_list_mayor
                FROM admin a
                LEFT JOIN user_permissions p ON p.user_type = 'admin' AND p.user_id = a.admin_id

                UNION ALL

                SELECT 
                    'mayor' AS user_type, 
                    cm.mayor_id AS user_id, 
                    cm.name, 
                    cm.name AS sort_name,
                    cm.email, 
                    'mayor' AS original_role,
                    c.section_name,
                    c.program,
                    cm.created_at,
                    p.permission_id,
                    p.is_mayor,
                    p.perm_qr_scanner,
                    p.perm_generate_report,
                    p.perm_declare_emergency,
                    p.perm_qr_codes,
                    p.perm_emergency_hotlines,
                    p.perm_my_qr,
                    p.perm_student_list_admin,
                    p.perm_student_list_mayor
                FROM class_mayor cm
                JOIN class c ON cm.class_id = c.class_id
                LEFT JOIN user_permissions p ON p.user_type = 'mayor' AND p.user_id = cm.mayor_id

                UNION ALL

                SELECT 
                    'student' AS user_type, 
                    s.student_id AS user_id, 
                    CONCAT(s.first_name, ' ', s.last_name) AS name, 
                    s.last_name AS sort_name,
                    s.email, 
                    'student' AS original_role,
                    c.section_name,
                    c.program,
                    s.created_at,
                    p.permission_id,
                    p.is_mayor,
                    p.perm_qr_scanner,
                    p.perm_generate_report,
                    p.perm_declare_emergency,
                    p.perm_qr_codes,
                    p.perm_emergency_hotlines,
                    p.perm_my_qr,
                    p.perm_student_list_admin,
                    p.perm_student_list_mayor
                FROM student s
                JOIN class c ON s.class_id = c.class_id
                LEFT JOIN user_permissions p ON p.user_type = 'student' AND p.user_id = s.student_id
                WHERE s.profile_status = 'Active'
                  AND NOT EXISTS (SELECT 1 FROM class_mayor cm2 WHERE cm2.student_id = s.student_id)

                ORDER BY CASE WHEN user_type = 'admin' THEN 0 ELSE 1 END, sort_name ASC";

        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get permissions for a specific user
     */
    public function getUserPermissions($userType, $userId) {
        $stmt = $this->query(
            "SELECT * FROM user_permissions WHERE user_type = :utype AND user_id = :uid",
            [':utype' => $userType, ':uid' => $userId]
        );
        return $stmt->fetch();
    }

    /**
     * Save (insert or update) permissions for a user
     */
    public function savePermissions($userType, $userId, $data) {
        $existing = $this->getUserPermissions($userType, $userId);

        if ($existing) {
            $sql = "UPDATE user_permissions SET
                        is_mayor = :is_mayor,
                        perm_qr_scanner = :perm_qr_scanner,
                        perm_generate_report = :perm_generate_report,
                        perm_declare_emergency = :perm_declare_emergency,
                        perm_qr_codes = :perm_qr_codes,
                        perm_emergency_hotlines = :perm_emergency_hotlines,
                        perm_my_qr = :perm_my_qr,
                        perm_student_list_admin = :perm_student_list_admin,
                        perm_student_list_mayor = :perm_student_list_mayor,
                        updated_by = :updated_by
                    WHERE user_type = :user_type AND user_id = :user_id";
        } else {
            $sql = "INSERT INTO user_permissions 
                        (user_type, user_id, is_mayor, perm_qr_scanner, perm_generate_report, 
                         perm_declare_emergency, perm_qr_codes, perm_emergency_hotlines, 
                         perm_my_qr, perm_student_list_admin, perm_student_list_mayor, updated_by)
                    VALUES 
                        (:user_type, :user_id, :is_mayor, :perm_qr_scanner, :perm_generate_report,
                         :perm_declare_emergency, :perm_qr_codes, :perm_emergency_hotlines,
                         :perm_my_qr, :perm_student_list_admin, :perm_student_list_mayor, :updated_by)";
        }

        $params = [
            ':user_type'              => $userType,
            ':user_id'                => $userId,
            ':is_mayor'               => (int)($data['is_mayor'] ?? 0),
            ':perm_qr_scanner'        => (int)($data['perm_qr_scanner'] ?? 0),
            ':perm_generate_report'   => (int)($data['perm_generate_report'] ?? 0),
            ':perm_declare_emergency' => (int)($data['perm_declare_emergency'] ?? 0),
            ':perm_qr_codes'          => (int)($data['perm_qr_codes'] ?? 0),
            ':perm_emergency_hotlines'=> (int)($data['perm_emergency_hotlines'] ?? 0),
            ':perm_my_qr'             => (int)($data['perm_my_qr'] ?? 0),
            ':perm_student_list_admin'=> (int)($data['perm_student_list_admin'] ?? 0),
            ':perm_student_list_mayor'=> (int)($data['perm_student_list_mayor'] ?? 0),
            ':updated_by'             => (int)($data['updated_by'] ?? 0),
        ];

        return $this->query($sql, $params);
    }
}
