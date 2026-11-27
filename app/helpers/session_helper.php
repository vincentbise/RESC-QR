<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? '';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? '';
}

function getUserAvatar() {
    return $_SESSION['user_avatar'] ?? null;
}

function getUserOriginalRole() {
    return $_SESSION['user_original_role'] ?? $_SESSION['user_role'] ?? '';
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Load RBAC permissions for the current user (cached per request).
 * Returns the permission row array, or null if no row exists.
 */
function getUserPermissions() {
    static $loaded = false;
    static $perms  = null;

    if (!$loaded) {
        $loaded = true;
        $originalRole = getUserOriginalRole();
        $userId       = getUserId();

        if ($originalRole && $userId) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM user_permissions WHERE user_type = :utype AND user_id = :uid LIMIT 1"
            );
            $stmt->execute([':utype' => $originalRole, ':uid' => $userId]);
            $row = $stmt->fetch();
            $perms = $row ?: null;
        }
    }

    return $perms;
}

/**
 * Default permissions that each role gets out-of-the-box
 * (when no user_permissions row exists, they keep their normal sidebar).
 */
function getRoleDefaultPermissions($role) {
    $defaults = [
        'admin' => [
            'perm_student_list_admin' => 1,
            'perm_qr_codes'           => 1,
            'perm_declare_emergency'  => 1,
            'perm_generate_report'    => 1,
        ],
        'mayor' => [
            'perm_qr_scanner'         => 1,
            'perm_student_list_mayor' => 1,
            'perm_emergency_hotlines' => 1,
            'perm_my_qr'              => 1,
        ],
        'student' => [
            'perm_my_qr'              => 1,
            'perm_emergency_hotlines' => 1,
        ],
    ];

    return $defaults[$role] ?? [];
}

/**
 * Check if the current user has a specific RBAC permission.
 *
 * Logic:
 *  - Role defaults ALWAYS apply (admin keeps their full sidebar).
 *  - RBAC row can only ADD extra permissions, never revoke defaults.
 */
function hasPermission($permKey) {
    $role     = getUserRole();
    $defaults = getRoleDefaultPermissions($role);

    // Role defaults always win — can never be revoked
    if (!empty($defaults[$permKey])) {
        return true;
    }

    // Check explicit RBAC grants (extra permissions beyond the role)
    $perms = getUserPermissions();
    if ($perms !== null) {
        return !empty($perms[$permKey]);
    }

    return false;
}