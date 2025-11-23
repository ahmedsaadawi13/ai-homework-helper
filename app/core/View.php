<?php
// FILE: /app/core/View.php

/**
 * View class for rendering views
 */
class View
{
    /**
     * Render a view
     *
     * @param string $view View name
     * @param array $data Data to pass to view
     */
    public static function render($view, $data = [])
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
     * Escape HTML output
     *
     * @param string $string String to escape
     * @return string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format date
     *
     * @param string $date Date string
     * @param string $format Format
     * @return string
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($date));
    }

    /**
     * Generate CSRF token field
     *
     * @return string
     */
    public static function csrfField()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    /**
     * Get and clear flash message
     *
     * @param string $type Message type
     * @return string|null
     */
    public static function flash($type = null)
    {
        if ($type === null) {
            // Return all flash messages
            $messages = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $messages;
        }

        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
