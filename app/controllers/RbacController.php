<?php

class RbacController extends Controller {

    private $permModel;

    public function __construct() {
        $this->requireAuth(['admin']);
        $this->permModel = $this->model('UserPermission');
    }

    /**
     * GET /rbac — show the user management / permissions page
     */
    public function index() {
        $users = $this->permModel->getAllUsersWithPermissions();

        $data = [
            'pageTitle'   => 'User Access Control',
            'users'       => $users,
            'permissions' => UserPermission::PERMISSIONS,
            'extraCss'    => ['rbac.css'],
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/rbac', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * POST /rbac/update — AJAX update permissions for a single user
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }

        $input = $this->getJsonInput();

        $userType = $input['user_type'] ?? '';
        $userId   = InputValidator::validateId($input['user_id'] ?? null);

        if (!in_array($userType, ['admin', 'mayor', 'student'])) {
            $this->json(['success' => false, 'message' => 'Invalid user type.'], 400);
            return;
        }

        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Invalid user ID.'], 400);
            return;
        }

        $existing = $this->permModel->getUserPermissions($userType, $userId);
        $defaultIsMayor = ($userType === 'mayor') ? 1 : 0;
        $preservedIsMayor = array_key_exists('is_mayor', $input)
            ? (int)$input['is_mayor']
            : ($existing ? (int)$existing['is_mayor'] : $defaultIsMayor);

        $data = [
            'is_mayor'               => $preservedIsMayor,
            'perm_qr_scanner'        => $input['perm_qr_scanner'] ?? 0,
            'perm_generate_report'   => $input['perm_generate_report'] ?? 0,
            'perm_declare_emergency' => $input['perm_declare_emergency'] ?? 0,
            'perm_qr_codes'          => $input['perm_qr_codes'] ?? 0,
            'perm_emergency_hotlines'=> $input['perm_emergency_hotlines'] ?? 0,
            'perm_my_qr'             => 1, // always on; no longer configurable in Access Control
            'perm_student_list_admin'=> $input['perm_student_list_admin'] ?? 0,
            'perm_student_list_mayor'=> $input['perm_student_list_mayor'] ?? 0,
            'updated_by'             => getUserId(),
        ];

        $this->permModel->savePermissions($userType, $userId, $data);

        $this->json([
            'success' => true,
            'message' => 'Permissions updated successfully.',
        ]);
    }

    /**
     * POST /rbac/defaults — AJAX get mayor default permissions
     */
    public function defaults() {
        $this->json([
            'success'  => true,
            'defaults' => UserPermission::MAYOR_DEFAULTS,
        ]);
    }
}
