<?php

class ClassroomController extends Controller {

    private $courseModel;
    private $classModel;

    public function __construct() {
        $this->requireAuth(['admin']);
        $this->courseModel = $this->model('CourseModel');
        $this->classModel  = $this->model('ClassModel');
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        $courses = $this->courseModel->getAllWithCounts();
        $classes = $this->classModel->getClassesWithMayorsAndCounts();

        // Active students along with their effective mayor flag for the cascading mayor selector
        $stmt = $db->query(
            "SELECT s.student_id, s.class_id, s.first_name, s.last_name, s.email, s.phone,
                    CASE
                        WHEN (cm.mayor_id IS NOT NULL AND COALESCE(up_m.is_mayor, 1) = 1)
                          OR COALESCE(up_s.is_mayor, 0) = 1
                        THEN 1 ELSE 0
                    END AS is_current_mayor
             FROM student s
             LEFT JOIN class_mayor cm ON cm.student_id = s.student_id
             LEFT JOIN user_permissions up_m ON up_m.user_type = 'mayor' AND up_m.user_id = cm.mayor_id
             LEFT JOIN user_permissions up_s ON up_s.user_type = 'student' AND up_s.user_id = s.student_id
             WHERE s.profile_status = 'Active'
             ORDER BY s.last_name ASC, s.first_name ASC"
        );
        $students = $stmt->fetchAll();

        $data = [
            'pageTitle' => 'Classroom Management',
            'courses'   => $courses,
            'classes'   => $classes,
            'students'  => $students,
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/classrooms', $data);
        $this->view('layouts/footer', $data);
    }

    public function storeCourse() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }
        $this->validateCSRF();

        $code  = strtoupper(InputValidator::sanitizeString($_POST['course_code'] ?? ''));
        $major = InputValidator::sanitizeString($_POST['course_major'] ?? '');

        if ($code === '') {
            $this->json(['success' => false, 'message' => 'Course code is required.'], 422);
            return;
        }

        if ($this->courseModel->existsByCodeAndMajor($code, $major)) {
            $label = $major !== '' ? $code . ' major in ' . $major : $code;
            $this->json(['success' => false, 'message' => $label . ' already exists.'], 422);
            return;
        }

        $newId = $this->courseModel->createCourse($code, $major);
        $this->json(['success' => true, 'message' => 'Course added successfully.', 'course_id' => (int)$newId]);
    }

    public function deleteCourse($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }
        $this->validateCSRF();

        $id = InputValidator::validateId($id);
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid course ID.'], 400);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM student s
             JOIN class c ON s.class_id = c.class_id
             WHERE c.course_id = :cid AND s.profile_status = 'Active'"
        );
        $stmt->execute([':cid' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->json(['success' => false, 'message' => 'Cannot delete a course that still has active students.'], 422);
            return;
        }

        $this->courseModel->deleteCourse($id);
        $this->json(['success' => true, 'message' => 'Course deleted successfully.']);
    }

    public function storeClass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }
        $this->validateCSRF();

        $courseId    = InputValidator::validateId($_POST['course_id'] ?? '');
        $yearLevel   = InputValidator::sanitizeString($_POST['year_level'] ?? '');
        $sectionName = InputValidator::sanitizeString($_POST['section_name'] ?? '');

        if (!$courseId || $yearLevel === '' || $sectionName === '') {
            $this->json(['success' => false, 'message' => 'Course, year level, and section name are required.'], 422);
            return;
        }

        $course = $this->courseModel->findByIdCourse($courseId);
        if (!$course) {
            $this->json(['success' => false, 'message' => 'Selected course not found.'], 404);
            return;
        }

        if ($this->classModel->sectionExists($sectionName, $courseId)) {
            $this->json(['success' => false, 'message' => 'Section "' . $sectionName . '" already exists in this course.'], 422);
            return;
        }

        $this->classModel->createClass([
            'course_id'    => $courseId,
            'section_name' => $sectionName,
            'program'      => $course['course_name'],
            'year_level'   => $yearLevel,
        ]);

        $this->json(['success' => true, 'message' => 'Class section added successfully.']);
    }

    public function deleteClass($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }
        $this->validateCSRF();

        $id = InputValidator::validateId($id);
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid class ID.'], 400);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM student WHERE class_id = :cid AND profile_status = 'Active'");
        $stmt->execute([':cid' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->json(['success' => false, 'message' => 'Cannot delete a section that still has active students.'], 422);
            return;
        }

        $this->classModel->deleteClass($id);
        $this->json(['success' => true, 'message' => 'Class section deleted successfully.']);
    }

    public function assignMayor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
            return;
        }
        $this->validateCSRF();

        $classId   = InputValidator::validateId($_POST['class_id'] ?? '');
        $studentId = !empty($_POST['student_id']) ? InputValidator::validateId($_POST['student_id']) : null;

        if (!$classId) {
            $this->json(['success' => false, 'message' => 'Please select a class section.'], 422);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $permModel = $this->model('UserPermission');
        $adminId = getUserId();

        // Validate selected student belongs to this section (if assigning one)
        if ($studentId) {
            $stuStmt = $db->prepare(
                "SELECT student_id, class_id, first_name, last_name FROM student WHERE student_id = :sid AND profile_status = 'Active' LIMIT 1"
            );
            $stuStmt->execute([':sid' => $studentId]);
            $selectedStudent = $stuStmt->fetch();
            if (!$selectedStudent || (int)$selectedStudent['class_id'] !== (int)$classId) {
                $this->json(['success' => false, 'message' => 'Selected student does not belong to this section.'], 422);
                return;
            }
        }

        // Helper closure to update a user's mayor status while preserving unrelated RBAC permissions
        $setMayorState = function ($userType, $userId, $makeMayor) use ($permModel, $adminId) {
            $existing = $permModel->getUserPermissions($userType, $userId);
            $data = [
                'is_mayor'                => $makeMayor ? 1 : 0,
                'perm_qr_scanner'         => $makeMayor ? 1 : 0,
                'perm_generate_report'    => $existing ? (int)$existing['perm_generate_report'] : 0,
                'perm_declare_emergency'  => $existing ? (int)$existing['perm_declare_emergency'] : 0,
                'perm_qr_codes'           => $existing ? (int)$existing['perm_qr_codes'] : 0,
                'perm_emergency_hotlines' => 1,
                'perm_my_qr'              => 1,
                'perm_student_list_admin' => $existing ? (int)$existing['perm_student_list_admin'] : 0,
                'perm_student_list_mayor' => $makeMayor ? 1 : 0,
                'updated_by'              => $adminId,
            ];
            $permModel->savePermissions($userType, $userId, $data);
        };

        // 1. Demote any current mayor(s) in this section (other than the newly selected student)
        $sectionStudents = $db->prepare(
            "SELECT s.student_id, cm.mayor_id
             FROM student s
             LEFT JOIN class_mayor cm ON cm.student_id = s.student_id
             WHERE s.class_id = :cid AND s.profile_status = 'Active'"
        );
        $sectionStudents->execute([':cid' => $classId]);
        foreach ($sectionStudents->fetchAll() as $row) {
            if ($studentId && (int)$row['student_id'] === (int)$studentId) {
                continue;
            }
            if (!empty($row['mayor_id'])) {
                $existingMayorPerm = $permModel->getUserPermissions('mayor', $row['mayor_id']);
                // Only demote if currently a mayor (either no row yet or is_mayor = 1)
                if (!$existingMayorPerm || !empty($existingMayorPerm['is_mayor'])) {
                    $setMayorState('mayor', $row['mayor_id'], false);
                }
            } else {
                $existingStuPerm = $permModel->getUserPermissions('student', $row['student_id']);
                if ($existingStuPerm && !empty($existingStuPerm['is_mayor'])) {
                    $setMayorState('student', $row['student_id'], false);
                }
            }
        }

        // Also demote any unlinked class_mayor in this class_id just in case
        $unlinkedMayors = $db->prepare("SELECT mayor_id FROM class_mayor WHERE class_id = :cid AND student_id IS NULL");
        $unlinkedMayors->execute([':cid' => $classId]);
        foreach ($unlinkedMayors->fetchAll() as $um) {
            $setMayorState('mayor', $um['mayor_id'], false);
        }

        // 2. Promote the selected student (or return cleared message)
        if (!$studentId) {
            $this->json(['success' => true, 'message' => 'Mayor assignment cleared for this section.']);
            return;
        }

        $linkedMayor = $db->prepare("SELECT mayor_id FROM class_mayor WHERE student_id = :sid LIMIT 1");
        $linkedMayor->execute([':sid' => $studentId]);
        $mayorId = $linkedMayor->fetchColumn();

        if ($mayorId) {
            $setMayorState('mayor', $mayorId, true);
        } else {
            $setMayorState('student', $studentId, true);
        }

        $this->json([
            'success' => true,
            'message' => $selectedStudent['first_name'] . ' ' . $selectedStudent['last_name'] . ' assigned as Class Mayor. Changes take effect on their next login.',
        ]);
    }
}
