<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller class
 * All controllers should extend this class
 */
class Controller
{
    /**
     * Load a model
     *
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model)
    {
        $modelPath = __DIR__ . '/../models/' . $model . '.php';

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }

        throw new Exception("Model {$model} not found");
    }

    /**
     * Load a view
     *
     * @param string $view View name
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = [])
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            throw new Exception("View {$view} not found");
        }
    }

    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     */
    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Get POST data with sanitization
     *
     * @param string $key Field key
     * @param mixed $default Default value
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    /**
     * Get GET data with sanitization
     *
     * @param string $key Field key
     * @param mixed $default Default value
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Data to sanitize
     * @return mixed
     */
    private function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Set flash message
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    protected function flash($type, $message)
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Validate CSRF token
     *
     * @return bool
     */
    protected function validateCsrfToken()
    {
        $token = $_POST['csrf_token'] ?? '';
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }

    /**
     * Check if user has specific role
     *
     * @param string|array $roles Role or array of roles
     * @return bool
     */
    protected function hasRole($roles)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? '';

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    /**
     * Require specific role
     *
     * @param string|array $roles Role or array of roles
     */
    protected function requireRole($roles)
    {
        if (!$this->hasRole($roles)) {
            $this->flash('error', 'You do not have permission to access this page');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Get current tenant ID
     *
     * @return int|null
     */
    protected function getTenantId()
    {
        return $_SESSION['tenant_id'] ?? null;
    }

    /**
     * JSON response
     *
     * @param array $data Data to return
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
