<?php
// FILE: /app/controllers/StudentController.php

/**
 * Student Controller
 * Handles student CRUD operations
 */
class StudentController extends Controller
{
    /**
     * List all students
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $studentModel = $this->model('Student');

        $page = max(1, (int) $this->get('page', 1));
        $perPage = 20;
        $search = $this->get('search', '');

        $students = $studentModel->getWithPagination($perPage, ($page - 1) * $perPage, $search);
        $totalStudents = $search ? count($students) : $studentModel->count();

        $paginator = new Paginator($totalStudents, $perPage, $page);

        $data = [
            'students' => $students,
            'paginator' => $paginator,
            'search' => $search,
        ];

        $this->view('students/index', $data);
    }

    /**
     * Show student details
     */
    public function show($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher', 'student']);

        $studentModel = $this->model('Student');
        $student = $studentModel->getWithDetails($id);

        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }

        // Get performance stats
        $stats = $studentModel->getPerformanceStats($id);

        // Get recent homework
        $homeworkModel = $this->model('Homework');
        $recentHomework = $homeworkModel->findAll(['student_id' => $id], 'created_at DESC', 10);

        $data = [
            'student' => $student,
            'stats' => $stats,
            'recent_homework' => $recentHomework,
        ];

        $this->view('students/show', $data);
    }

    /**
     * Show create student form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        // Check subscription limits
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'students')) {
            $this->flash('error', 'You have reached your student limit. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $classModel = $this->model('ClassModel');
        $classes = $classModel->findAll(['status' => 'active'], 'name ASC');

        $data = ['classes' => $classes];

        $this->view('students/create', $data);
    }

    /**
     * Store new student
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/students/create');
            return;
        }

        // Check subscription limits
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'students')) {
            $this->flash('error', 'You have reached your student limit. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $classId = $this->post('class_id');
        $gradeLevel = $this->post('grade_level');
        $parentName = $this->post('parent_name');
        $parentEmail = $this->post('parent_email');
        $parentPhone = $this->post('parent_phone');

        // Validate
        $validator = new Validator(compact('name'));
        $validator->required('name')->min('name', 2);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/students/create');
            return;
        }

        $studentModel = $this->model('Student');
        $studentId = $studentModel->create([
            'name' => $name,
            'email' => $email,
            'class_id' => $classId ?: null,
            'grade_level' => $gradeLevel,
            'parent_name' => $parentName,
            'parent_email' => $parentEmail,
            'parent_phone' => $parentPhone,
            'status' => 'active',
        ]);

        if ($studentId) {
            $this->flash('success', 'Student created successfully');
            $this->redirect('/students/' . $studentId);
        } else {
            $this->flash('error', 'Failed to create student');
            $this->redirect('/students/create');
        }
    }

    /**
     * Show edit student form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $studentModel = $this->model('Student');
        $student = $studentModel->findById($id);

        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }

        $classModel = $this->model('ClassModel');
        $classes = $classModel->findAll(['status' => 'active'], 'name ASC');

        $data = [
            'student' => $student,
            'classes' => $classes,
        ];

        $this->view('students/edit', $data);
    }

    /**
     * Update student
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/students/' . $id . '/edit');
            return;
        }

        $studentModel = $this->model('Student');

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'class_id' => $this->post('class_id') ?: null,
            'grade_level' => $this->post('grade_level'),
            'parent_name' => $this->post('parent_name'),
            'parent_email' => $this->post('parent_email'),
            'parent_phone' => $this->post('parent_phone'),
            'status' => $this->post('status', 'active'),
        ];

        if ($studentModel->update($id, $data)) {
            $this->flash('success', 'Student updated successfully');
            $this->redirect('/students/' . $id);
        } else {
            $this->flash('error', 'Failed to update student');
            $this->redirect('/students/' . $id . '/edit');
        }
    }

    /**
     * Delete student
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/students');
            return;
        }

        $studentModel = $this->model('Student');

        if ($studentModel->delete($id)) {
            $this->flash('success', 'Student deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete student');
        }

        $this->redirect('/students');
    }
}
