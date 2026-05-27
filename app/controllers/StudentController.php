<?php

class StudentController extends Controller {

    private $studentModel;
    private $classModel;

    public function __construct() {
        $this->requireAuth(['admin']);
        $this->studentModel = $this->model('Student');
        $this->classModel   = $this->model('ClassModel');
    }

    public function index() {
        $search  = InputValidator::sanitizeString($_GET['search'] ?? '');
        $classId = InputValidator::validateId($_GET['class_id'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $totalStudents = $this->studentModel->countFiltered($search, $classId ?: null);
        $totalPages    = max(1, (int)ceil($totalStudents / $perPage));
        $page          = min($page, $totalPages);
        $offset        = ($page - 1) * $perPage;

        $students = $this->studentModel->getAllPaginated($search, $classId ?: null, $perPage, $offset);
        $classes  = $this->classModel->getAll();

        if ($this->isAjax()) {
            $this->json([
                'success'       => true,
                'students'      => $students,
                'total'         => $totalStudents,
                'page'          => $page,
                'per_page'      => $perPage,
                'total_pages'   => $totalPages,
            ]);
            return;
        }

        $data = [
            'pageTitle'     => 'Students',
            'students'      => $students,
            'classes'       => $classes,
            'search'        => $search,
            'classId'       => $classId,
            'page'          => $page,
            'perPage'       => $perPage,
            'totalStudents' => $totalStudents,
            'totalPages'    => $totalPages,
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/students/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function create() {
        $classes = $this->classModel->getAll();
        $data = ['pageTitle' => 'Add Student', 'classes' => $classes];

        $this->view('layouts/header', $data);
        $this->view('admin/students/create', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student');
            return;
        }

        $firstName = InputValidator::validateName($_POST['first_name'] ?? '');
        $lastName  = InputValidator::validateName($_POST['last_name'] ?? '');
        $email     = InputValidator::validateEmail($_POST['email'] ?? '');
        $phone     = InputValidator::sanitizeString($_POST['phone'] ?? '');
        $course    = InputValidator::sanitizeString($_POST['course'] ?? '');
        $yearLevel = InputValidator::sanitizeString($_POST['year_level'] ?? '');
        $classId   = InputValidator::validateId($_POST['class_id'] ?? '');

        if (!$firstName || !$lastName || !$classId || !$course || !$yearLevel) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please fill in all required fields.'], 422);
                return;
            }
            $this->setFlash('error', 'Please fill in all required fields.');
            $this->redirect('/student/create');
            return;
        }

        $id = $this->studentModel->create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email ?: null,
            'phone'      => $phone ?: null,
            'course'     => $course,
            'year_level' => $yearLevel,
            'class_id'   => $classId,
        ]);

        if ($this->isAjax()) {
            $student = $this->studentModel->findStudentById($id);
            $this->json(['success' => true, 'message' => 'Student registered successfully.', 'student' => $student]);
            return;
        }

        $this->setFlash('success', 'Student registered successfully.');
        $this->redirect('/student');
    }

    public function edit($id = null) {
        $id = InputValidator::validateId($id);
        if (!$id) { $this->redirect('/student'); return; }

        $student = $this->studentModel->findStudentById($id);
        if (!$student) { $this->redirect('/student'); return; }

        $classes = $this->classModel->getAll();
        $data = ['pageTitle' => 'Edit Student', 'student' => $student, 'classes' => $classes];

        $this->view('layouts/header', $data);
        $this->view('admin/students/edit', $data);
        $this->view('layouts/footer', $data);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/student'); return; }

        $id = InputValidator::validateId($id);
        if (!$id) {
            if ($this->isAjax()) { $this->json(['success' => false, 'message' => 'Invalid ID'], 400); return; }
            $this->redirect('/student');
            return;
        }

        $data = [];
        if (isset($_POST['first_name'])) $data['first_name'] = InputValidator::validateName($_POST['first_name']);
        if (isset($_POST['last_name']))  $data['last_name']  = InputValidator::validateName($_POST['last_name']);
        if (isset($_POST['email']))      $data['email']      = InputValidator::validateEmail($_POST['email']) ?: null;
        if (isset($_POST['phone']))      $data['phone']      = InputValidator::sanitizeString($_POST['phone']);
        if (isset($_POST['course']))     $data['course']     = InputValidator::sanitizeString($_POST['course']);
        if (isset($_POST['year_level'])) $data['year_level'] = InputValidator::sanitizeString($_POST['year_level']);
        if (isset($_POST['class_id']))   $data['class_id']   = InputValidator::validateId($_POST['class_id']);

        $data = array_filter($data, fn($v) => $v !== false && $v !== null);

        $this->studentModel->updateStudent($id, $data);

        if ($this->isAjax()) {
            $student = $this->studentModel->findStudentById($id);
            $this->json(['success' => true, 'message' => 'Student updated successfully.', 'student' => $student]);
            return;
        }

        $this->setFlash('success', 'Student updated successfully.');
        $this->redirect('/student');
    }

    public function delete($id = null) {
        $id = InputValidator::validateId($id);
        if (!$id) {
            if ($this->isAjax()) { $this->json(['success' => false, 'message' => 'Invalid ID'], 400); return; }
            $this->redirect('/student');
            return;
        }

        $this->studentModel->deleteStudent($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Student removed successfully.']);
            return;
        }

        $this->setFlash('success', 'Student removed successfully.');
        $this->redirect('/student');
    }
}
