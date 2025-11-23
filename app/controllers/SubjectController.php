<?php
// FILE: /app/controllers/SubjectController.php

/**
 * Subject Controller
 * Handles subject CRUD operations
 */
class SubjectController extends Controller
{
    /**
     * List all subjects
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $subjectModel = $this->model('Subject');
        $subjects = $subjectModel->findAll(['status' => 'active'], 'name ASC');

        $data = ['subjects' => $subjects];

        $this->view('subjects/index', $data);
    }

    /**
     * Show create subject form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $this->view('subjects/create');
    }

    /**
     * Store new subject
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/subjects/create');
            return;
        }

        $name = $this->post('name');
        $code = $this->post('code');
        $description = $this->post('description');

        // Validate
        $validator = new Validator(compact('name'));
        $validator->required('name')->min('name', 2);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/subjects/create');
            return;
        }

        $subjectModel = $this->model('Subject');
        $subjectId = $subjectModel->create([
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'status' => 'active',
        ]);

        if ($subjectId) {
            $this->flash('success', 'Subject created successfully');
            $this->redirect('/subjects');
        } else {
            $this->flash('error', 'Failed to create subject');
            $this->redirect('/subjects/create');
        }
    }

    /**
     * Show edit subject form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $subjectModel = $this->model('Subject');
        $subject = $subjectModel->findById($id);

        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/subjects');
            return;
        }

        $data = ['subject' => $subject];

        $this->view('subjects/edit', $data);
    }

    /**
     * Update subject
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/subjects/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'code' => $this->post('code'),
            'description' => $this->post('description'),
            'status' => $this->post('status', 'active'),
        ];

        $subjectModel = $this->model('Subject');

        if ($subjectModel->update($id, $data)) {
            $this->flash('success', 'Subject updated successfully');
            $this->redirect('/subjects');
        } else {
            $this->flash('error', 'Failed to update subject');
            $this->redirect('/subjects/' . $id . '/edit');
        }
    }

    /**
     * Delete subject
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/subjects');
            return;
        }

        $subjectModel = $this->model('Subject');

        if ($subjectModel->delete($id)) {
            $this->flash('success', 'Subject deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete subject');
        }

        $this->redirect('/subjects');
    }
}
