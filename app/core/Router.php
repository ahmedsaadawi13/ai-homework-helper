<?php
// FILE: /app/core/Router.php

/**
 * Router class for handling URL routing
 * Maps URLs to controllers and methods
 */
class Router
{
    private $routes = [];
    private $controller = 'DashboardController';
    private $method = 'index';
    private $params = [];

    /**
     * Add a route to the router
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $uri URI pattern
     * @param string $controller Controller class name
     * @param string $action Controller method name
     */
    public function add($method, $uri, $controller, $action)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Route the request to appropriate controller
     */
    public function dispatch()
    {
        $requestUri = $this->getUri();
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Check for matching route
        foreach ($this->routes as $route) {
            $pattern = $this->convertUriToRegex($route['uri']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                $this->controller = $route['controller'];
                $this->method = $route['action'];
                $this->params = $matches;
                break;
            }
        }

        $this->execute();
    }

    /**
     * Execute the controller method
     */
    private function execute()
    {
        $controllerPath = __DIR__ . '/../controllers/' . $this->controller . '.php';

        if (!file_exists($controllerPath)) {
            $this->showError404();
            return;
        }

        require_once $controllerPath;

        if (!class_exists($this->controller)) {
            $this->showError404();
            return;
        }

        $controllerInstance = new $this->controller();

        if (!method_exists($controllerInstance, $this->method)) {
            $this->showError404();
            return;
        }

        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    /**
     * Get clean URI from request
     *
     * @return string
     */
    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $uri = strtok($uri, '?');

        // Remove base path if exists
        $basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Convert URI pattern to regex pattern
     *
     * @param string $uri
     * @return string
     */
    private function convertUriToRegex($uri)
    {
        // Convert {id} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Show 404 error page
     */
    private function showError404()
    {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        exit;
    }
}
