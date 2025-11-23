<?php
// FILE: /app/controllers/TenantController.php

/**
 * Tenant Controller
 * Platform admin only - manages tenants
 */
class TenantController extends Controller
{
    /**
     * List all tenants
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole('platform_admin');

        $tenantModel = $this->model('Tenant');
        $tenants = $tenantModel->findAll([], 'created_at DESC');

        $data = ['tenants' => $tenants];

        $this->view('admin/tenants/index', $data);
    }

    /**
     * Show create tenant form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole('platform_admin');

        $this->view('admin/tenants/create');
    }

    /**
     * Store new tenant
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole('platform_admin');

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/tenants/create');
            return;
        }

        $name = $this->post('name');
        $type = $this->post('type');
        $email = $this->post('email');

        $tenantModel = $this->model('Tenant');
        $apiKey = $tenantModel->generateApiKey();

        $tenantId = $tenantModel->create([
            'name' => $name,
            'type' => $type,
            'email' => $email,
            'status' => 'active',
            'api_key' => $apiKey,
        ]);

        if ($tenantId) {
            // Assign free plan
            $subscriptionModel = $this->model('TenantSubscription');
            $subscriptionModel->changePlan($tenantId, 1);

            $this->flash('success', 'Tenant created successfully');
            $this->redirect('/admin/tenants');
        } else {
            $this->flash('error', 'Failed to create tenant');
            $this->redirect('/admin/tenants/create');
        }
    }

    /**
     * Show edit tenant form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole('platform_admin');

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->findById($id);

        if (!$tenant) {
            $this->flash('error', 'Tenant not found');
            $this->redirect('/admin/tenants');
            return;
        }

        $data = ['tenant' => $tenant];

        $this->view('admin/tenants/edit', $data);
    }

    /**
     * Update tenant
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole('platform_admin');

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/tenants/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'type' => $this->post('type'),
            'email' => $this->post('email'),
            'status' => $this->post('status'),
        ];

        $tenantModel = $this->model('Tenant');

        if ($tenantModel->update($id, $data)) {
            $this->flash('success', 'Tenant updated successfully');
            $this->redirect('/admin/tenants');
        } else {
            $this->flash('error', 'Failed to update tenant');
            $this->redirect('/admin/tenants/' . $id . '/edit');
        }
    }
}
