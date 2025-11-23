<?php
// FILE: /app/controllers/UserController.php

/**
 * User Controller
 * Handles user management
 */
class UserController extends Controller
{
    /**
     * List all users
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        $userModel = $this->model('User');

        if (Auth::isPlatformAdmin()) {
            $users = $userModel->findAll([], 'created_at DESC');
        } else {
            $users = $userModel->getByTenant(Auth::tenantId());
        }

        $data = ['users' => $users];

        $this->view('admin/users/index', $data);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        $this->view('admin/users/create');
    }

    /**
     * Store new user
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/users/create');
            return;
        }

        $userModel = $this->model('User');

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'role' => $this->post('role'),
            'status' => 'active',
        ];

        // Tenant admin can only create users within their tenant
        if (!Auth::isPlatformAdmin()) {
            $data['tenant_id'] = Auth::tenantId();
        }

        if ($userModel->emailExists($data['email'])) {
            $this->flash('error', 'Email already exists');
            $this->redirect('/admin/users/create');
            return;
        }

        if ($userModel->create($data)) {
            $this->flash('success', 'User created successfully');
            $this->redirect('/admin/users');
        } else {
            $this->flash('error', 'Failed to create user');
            $this->redirect('/admin/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        $userModel = $this->model('User');
        $user = $userModel->findById($id);

        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        $data = ['user' => $user];

        $this->view('admin/users/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'status' => $this->post('status'),
        ];

        $password = $this->post('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        $userModel = $this->model('User');

        if ($userModel->update($id, $data)) {
            $this->flash('success', 'User updated successfully');
            $this->redirect('/admin/users');
        } else {
            $this->flash('error', 'Failed to update user');
            $this->redirect('/admin/users/' . $id . '/edit');
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin', 'tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/users');
            return;
        }

        // Prevent deleting self
        if ($id == Auth::id()) {
            $this->flash('error', 'You cannot delete your own account');
            $this->redirect('/admin/users');
            return;
        }

        $userModel = $this->model('User');

        if ($userModel->delete($id)) {
            $this->flash('success', 'User deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete user');
        }

        $this->redirect('/admin/users');
    }
}
