<?php
// FILE: /app/controllers/ClassController.php

/**
 * Class Controller
 * Handles class/group CRUD operations
 */
class ClassController extends Controller
{
    /**
     * List all classes
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $classModel = $this->model('ClassModel');
        $classes = $classModel->getAllWithCounts();

        $data = ['classes' => $classes];

        $this->view('classes/index', $data);
    }

    /**
     * Show create class form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        // Check subscription limits
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'classes')) {
            $this->flash('error', 'You have reached your class limit. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $this->view('classes/create');
    }

    /**
     * Store new class
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/classes/create');
            return;
        }

        // Check subscription limits
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'classes')) {
            $this->flash('error', 'You have reached your class limit. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $name = $this->post('name');
        $gradeLevel = $this->post('grade_level');
        $description = $this->post('description');

        // Validate
        $validator = new Validator(compact('name'));
        $validator->required('name')->min('name', 2);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/classes/create');
            return;
        }

        $classModel = $this->model('ClassModel');
        $classId = $classModel->create([
            'name' => $name,
            'grade_level' => $gradeLevel,
            'description' => $description,
            'status' => 'active',
        ]);

        if ($classId) {
            $this->flash('success', 'Class created successfully');
            $this->redirect('/classes');
        } else {
            $this->flash('error', 'Failed to create class');
            $this->redirect('/classes/create');
        }
    }

    /**
     * Show edit class form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $classModel = $this->model('ClassModel');
        $class = $classModel->findById($id);

        if (!$class) {
            $this->flash('error', 'Class not found');
            $this->redirect('/classes');
            return;
        }

        $data = ['class' => $class];

        $this->view('classes/edit', $data);
    }

    /**
     * Update class
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/classes/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'grade_level' => $this->post('grade_level'),
            'description' => $this->post('description'),
            'status' => $this->post('status', 'active'),
        ];

        $classModel = $this->model('ClassModel');

        if ($classModel->update($id, $data)) {
            $this->flash('success', 'Class updated successfully');
            $this->redirect('/classes');
        } else {
            $this->flash('error', 'Failed to update class');
            $this->redirect('/classes/' . $id . '/edit');
        }
    }

    /**
     * Delete class
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/classes');
            return;
        }

        $classModel = $this->model('ClassModel');

        if ($classModel->delete($id)) {
            $this->flash('success', 'Class deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete class');
        }

        $this->redirect('/classes');
    }
}
