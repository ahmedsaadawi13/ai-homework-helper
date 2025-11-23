<?php
// FILE: /app/core/App.php

/**
 * Application bootstrap class
 * Initializes the application and handles routing
 */
class App
{
    private $router;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->loadRoutes();
    }

    /**
     * Run the application
     */
    public function run()
    {
        $this->router->dispatch();
    }

    /**
     * Load application routes
     */
    private function loadRoutes()
    {
        // Authentication routes
        $this->router->add('GET', '/', 'HomeController', 'index');
        $this->router->add('GET', '/login', 'AuthController', 'login');
        $this->router->add('POST', '/login', 'AuthController', 'loginPost');
        $this->router->add('GET', '/logout', 'AuthController', 'logout');
        $this->router->add('GET', '/register', 'AuthController', 'register');
        $this->router->add('POST', '/register', 'AuthController', 'registerPost');

        // Dashboard
        $this->router->add('GET', '/dashboard', 'DashboardController', 'index');

        // Students
        $this->router->add('GET', '/students', 'StudentController', 'index');
        $this->router->add('GET', '/students/create', 'StudentController', 'create');
        $this->router->add('POST', '/students/create', 'StudentController', 'store');
        $this->router->add('GET', '/students/{id}', 'StudentController', 'show');
        $this->router->add('GET', '/students/{id}/edit', 'StudentController', 'edit');
        $this->router->add('POST', '/students/{id}/edit', 'StudentController', 'update');
        $this->router->add('POST', '/students/{id}/delete', 'StudentController', 'delete');

        // Teachers
        $this->router->add('GET', '/teachers', 'TeacherController', 'index');
        $this->router->add('GET', '/teachers/create', 'TeacherController', 'create');
        $this->router->add('POST', '/teachers/create', 'TeacherController', 'store');
        $this->router->add('GET', '/teachers/{id}/edit', 'TeacherController', 'edit');
        $this->router->add('POST', '/teachers/{id}/edit', 'TeacherController', 'update');
        $this->router->add('POST', '/teachers/{id}/delete', 'TeacherController', 'delete');

        // Classes
        $this->router->add('GET', '/classes', 'ClassController', 'index');
        $this->router->add('GET', '/classes/create', 'ClassController', 'create');
        $this->router->add('POST', '/classes/create', 'ClassController', 'store');
        $this->router->add('GET', '/classes/{id}/edit', 'ClassController', 'edit');
        $this->router->add('POST', '/classes/{id}/edit', 'ClassController', 'update');
        $this->router->add('POST', '/classes/{id}/delete', 'ClassController', 'delete');

        // Subjects
        $this->router->add('GET', '/subjects', 'SubjectController', 'index');
        $this->router->add('GET', '/subjects/create', 'SubjectController', 'create');
        $this->router->add('POST', '/subjects/create', 'SubjectController', 'store');
        $this->router->add('GET', '/subjects/{id}/edit', 'SubjectController', 'edit');
        $this->router->add('POST', '/subjects/{id}/edit', 'SubjectController', 'update');
        $this->router->add('POST', '/subjects/{id}/delete', 'SubjectController', 'delete');

        // Homework
        $this->router->add('GET', '/homework', 'HomeworkController', 'index');
        $this->router->add('GET', '/homework/create', 'HomeworkController', 'create');
        $this->router->add('POST', '/homework/create', 'HomeworkController', 'store');
        $this->router->add('GET', '/homework/{id}', 'HomeworkController', 'show');
        $this->router->add('POST', '/homework/{id}/ask-ai', 'HomeworkController', 'askAi');
        $this->router->add('POST', '/homework/{id}/status', 'HomeworkController', 'updateStatus');
        $this->router->add('POST', '/homework/{id}/delete', 'HomeworkController', 'delete');

        // AI Sessions
        $this->router->add('GET', '/ai/sessions', 'AiController', 'sessions');
        $this->router->add('GET', '/ai/quiz/generate', 'AiController', 'generateQuiz');
        $this->router->add('POST', '/ai/quiz/generate', 'AiController', 'generateQuizPost');
        $this->router->add('GET', '/ai/quiz/{id}', 'AiController', 'showQuiz');
        $this->router->add('POST', '/ai/quiz/{id}/submit', 'AiController', 'submitQuiz');

        // Admin - Subscriptions
        $this->router->add('GET', '/admin/subscription', 'SubscriptionController', 'index');
        $this->router->add('GET', '/admin/plans', 'SubscriptionController', 'plans');
        $this->router->add('POST', '/admin/subscription/change', 'SubscriptionController', 'changePlan');
        $this->router->add('GET', '/admin/billing', 'SubscriptionController', 'billing');
        $this->router->add('POST', '/admin/payment/simulate', 'SubscriptionController', 'simulatePayment');

        // Admin - Tenants (Platform Admin only)
        $this->router->add('GET', '/admin/tenants', 'TenantController', 'index');
        $this->router->add('GET', '/admin/tenants/create', 'TenantController', 'create');
        $this->router->add('POST', '/admin/tenants/create', 'TenantController', 'store');
        $this->router->add('GET', '/admin/tenants/{id}/edit', 'TenantController', 'edit');
        $this->router->add('POST', '/admin/tenants/{id}/edit', 'TenantController', 'update');

        // Admin - Users
        $this->router->add('GET', '/admin/users', 'UserController', 'index');
        $this->router->add('GET', '/admin/users/create', 'UserController', 'create');
        $this->router->add('POST', '/admin/users/create', 'UserController', 'store');
        $this->router->add('GET', '/admin/users/{id}/edit', 'UserController', 'edit');
        $this->router->add('POST', '/admin/users/{id}/edit', 'UserController', 'update');
        $this->router->add('POST', '/admin/users/{id}/delete', 'UserController', 'delete');

        // Reports
        $this->router->add('GET', '/reports/homework', 'ReportController', 'homework');
        $this->router->add('GET', '/reports/performance', 'ReportController', 'performance');
        $this->router->add('GET', '/reports/usage', 'ReportController', 'usage');

        // API
        $this->router->add('POST', '/api/v1/homework/create', 'ApiController', 'createHomework');
        $this->router->add('GET', '/api/v1/homework/{id}', 'ApiController', 'getHomework');
    }
}
