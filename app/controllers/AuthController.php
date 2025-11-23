<?php
// FILE: /app/controllers/AuthController.php

/**
 * Authentication Controller
 * Handles login, logout, and registration
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function login()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/login');
    }

    /**
     * Process login
     */
    public function loginPost()
    {
        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/login');
            return;
        }

        $email = $this->post('email');
        $password = $this->post('password');

        // Validate input
        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email')->email('email')
                  ->required('password');

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/login');
            return;
        }

        // Attempt authentication
        $user = Auth::attempt($email, $password);

        if ($user) {
            Auth::login($user);
            $this->flash('success', 'Welcome back, ' . $user['name'] . '!');
            $this->redirect('/dashboard');
        } else {
            $this->flash('error', 'Invalid email or password');
            $this->redirect('/login');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        $this->flash('success', 'You have been logged out');
        $this->redirect('/login');
    }

    /**
     * Show registration form
     */
    public function register()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/register');
    }

    /**
     * Process registration
     */
    public function registerPost()
    {
        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/register');
            return;
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');
        $tenantName = $this->post('tenant_name');
        $tenantType = $this->post('tenant_type', 'family');

        // Validate input
        $validator = new Validator([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'confirm_password' => $confirmPassword,
            'tenant_name' => $tenantName,
        ]);

        $validator->required('name')->min('name', 2)
                  ->required('email')->email('email')
                  ->required('password')->min('password', 6)
                  ->required('confirm_password')->match('confirm_password', 'password')
                  ->required('tenant_name')->min('tenant_name', 2);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/register');
            return;
        }

        // Check if email exists
        $userModel = $this->model('User');
        if ($userModel->emailExists($email)) {
            $this->flash('error', 'Email already exists');
            $this->redirect('/register');
            return;
        }

        // Create tenant
        $tenantModel = $this->model('Tenant');
        $apiKey = $tenantModel->generateApiKey();

        $tenantId = $tenantModel->create([
            'name' => $tenantName,
            'type' => $tenantType,
            'email' => $email,
            'status' => 'active',
            'api_key' => $apiKey,
        ]);

        if (!$tenantId) {
            $this->flash('error', 'Failed to create account');
            $this->redirect('/register');
            return;
        }

        // Assign free plan to tenant
        $subscriptionModel = $this->model('TenantSubscription');
        $subscriptionModel->changePlan($tenantId, 1); // Free plan

        // Create user
        $userId = $userModel->create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'tenant_admin',
            'status' => 'active',
        ]);

        if ($userId) {
            $this->flash('success', 'Account created successfully! Please login.');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'Failed to create user');
            $this->redirect('/register');
        }
    }
}
