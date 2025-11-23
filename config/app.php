<?php
// FILE: /config/app.php

/**
 * Application configuration
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'AI Homework Helper',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',

    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
        'name' => 'AIHH_SESSION',
    ],

    'upload' => [
        'max_size' => (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880), // 5MB
        'allowed_extensions' => explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,pdf'),
        'path' => __DIR__ . '/../storage/uploads/',
    ],

    'ai' => [
        'enabled' => filter_var($_ENV['AI_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'max_requests_per_month' => (int) ($_ENV['AI_MAX_REQUESTS_PER_MONTH'] ?? 1000),
    ],

    'pagination' => [
        'per_page' => 20,
    ],
];
