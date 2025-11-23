<?php
// FILE: /app/controllers/TeacherController.php

/**
 * Teacher Controller
 * Handles teacher management
 */
class TeacherController extends Controller
{
    /**
     * List all teachers
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $userModel = $this->model('User');
        $teachers = $userModel->getTeachers(Auth::tenantId());

        $data = ['teachers' => $teachers];

        $this->view('teachers/index', $data);
    }

    /**
     * Show create teacher form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $classModel = $this->model('ClassModel');
        $classes = $classModel->findAll(['status' => 'active'], 'name ASC');

        $data = ['classes' => $classes];

        $this->view('teachers/create', $data);
    }

    /**
     * Store new teacher
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/teachers/create');
            return;
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');

        // Validate
        $validator = new Validator(compact('name', 'email', 'password'));
        $validator->required('name')->min('name', 2)
                  ->required('email')->email('email')
                  ->required('password')->min('password', 6);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/teachers/create');
            return;
        }

        $userModel = $this->model('User');

        // Check if email exists
        if ($userModel->emailExists($email)) {
            $this->flash('error', 'Email already exists');
            $this->redirect('/teachers/create');
            return;
        }

        $teacherId = $userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
            'status' => 'active',
        ]);

        if ($teacherId) {
            $this->flash('success', 'Teacher created successfully');
            $this->redirect('/teachers');
        } else {
            $this->flash('error', 'Failed to create teacher');
            $this->redirect('/teachers/create');
        }
    }

    /**
     * Show edit teacher form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $userModel = $this->model('User');
        $teacher = $userModel->findById($id);

        if (!$teacher || $teacher['role'] !== 'teacher') {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/teachers');
            return;
        }

        $data = ['teacher' => $teacher];

        $this->view('teachers/edit', $data);
    }

    /**
     * Update teacher
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/teachers/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'status' => $this->post('status', 'active'),
        ];

        $password = $this->post('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        $userModel = $this->model('User');

        if ($userModel->update($id, $data)) {
            $this->flash('success', 'Teacher updated successfully');
            $this->redirect('/teachers');
        } else {
            $this->flash('error', 'Failed to update teacher');
            $this->redirect('/teachers/' . $id . '/edit');
        }
    }

    /**
     * Delete teacher
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/teachers');
            return;
        }

        $userModel = $this->model('User');

        if ($userModel->delete($id)) {
            $this->flash('success', 'Teacher deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete teacher');
        }

        $this->redirect('/teachers');
    }
}
