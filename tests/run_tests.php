<?php
// FILE: /tests/run_tests.php

/**
 * Simple Test Runner for AI Homework Helper
 * Run basic functional tests
 */

echo "AI Homework Helper - Test Suite\n";
echo "================================\n\n";

$passed = 0;
$failed = 0;

/**
 * Test helper function
 */
function test($name, $condition, $message = '') {
    global $passed, $failed;

    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name";
        if ($message) {
            echo " - $message";
        }
        echo "\n";
        $failed++;
    }
}

// Test 1: Core files exist
echo "Testing Core Files...\n";
test('Core Database class exists', file_exists(__DIR__ . '/../app/core/Database.php'));
test('Core Router class exists', file_exists(__DIR__ . '/../app/core/Router.php'));
test('Core Controller class exists', file_exists(__DIR__ . '/../app/core/Controller.php'));
test('Core Model class exists', file_exists(__DIR__ . '/../app/core/Model.php'));
test('Entry point exists', file_exists(__DIR__ . '/../public/index.php'));
echo "\n";

// Test 2: Configuration files exist
echo "Testing Configuration...\n";
test('.env.example exists', file_exists(__DIR__ . '/../.env.example'));
test('Database config exists', file_exists(__DIR__ . '/../config/database.php'));
test('App config exists', file_exists(__DIR__ . '/../config/app.php'));
echo "\n";

// Test 3: Model files exist
echo "Testing Models...\n";
test('User model exists', file_exists(__DIR__ . '/../app/models/User.php'));
test('Tenant model exists', file_exists(__DIR__ . '/../app/models/Tenant.php'));
test('Student model exists', file_exists(__DIR__ . '/../app/models/Student.php'));
test('Homework model exists', file_exists(__DIR__ . '/../app/models/Homework.php'));
test('Quiz model exists', file_exists(__DIR__ . '/../app/models/Quiz.php'));
echo "\n";

// Test 4: Controller files exist
echo "Testing Controllers...\n";
test('Auth controller exists', file_exists(__DIR__ . '/../app/controllers/AuthController.php'));
test('Dashboard controller exists', file_exists(__DIR__ . '/../app/controllers/DashboardController.php'));
test('Student controller exists', file_exists(__DIR__ . '/../app/controllers/StudentController.php'));
test('Homework controller exists', file_exists(__DIR__ . '/../app/controllers/HomeworkController.php'));
test('API controller exists', file_exists(__DIR__ . '/../app/controllers/ApiController.php'));
echo "\n";

// Test 5: Helper files exist
echo "Testing Helpers...\n";
test('Auth helper exists', file_exists(__DIR__ . '/../app/helpers/Auth.php'));
test('Validator helper exists', file_exists(__DIR__ . '/../app/helpers/Validator.php'));
test('FileUpload helper exists', file_exists(__DIR__ . '/../app/helpers/FileUpload.php'));
test('AiEngine helper exists', file_exists(__DIR__ . '/../app/helpers/AiEngine.php'));
echo "\n";

// Test 6: View files exist
echo "Testing Views...\n";
test('Login view exists', file_exists(__DIR__ . '/../app/views/auth/login.php'));
test('Dashboard view exists', file_exists(__DIR__ . '/../app/views/dashboard/tenant_admin.php'));
test('Student index view exists', file_exists(__DIR__ . '/../app/views/students/index.php'));
test('Homework show view exists', file_exists(__DIR__ . '/../app/views/homework/show.php'));
echo "\n";

// Test 7: Database schema exists
echo "Testing Database...\n";
test('Database schema file exists', file_exists(__DIR__ . '/../database.sql'));
echo "\n";

// Test 8: Storage directory
echo "Testing Storage...\n";
$storageDir = __DIR__ . '/../storage/uploads';
test('Storage directory exists', is_dir($storageDir));
test('Storage directory is writable', is_writable($storageDir));
echo "\n";

// Test 9: Public assets
echo "Testing Assets...\n";
test('CSS file exists', file_exists(__DIR__ . '/../public/css/style.css'));
test('JS file exists', file_exists(__DIR__ . '/../public/js/app.js'));
echo "\n";

// Summary
echo "================================\n";
echo "Test Results:\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";
echo "================================\n";

if ($failed === 0) {
    echo "\nAll tests passed! ✓\n";
    exit(0);
} else {
    echo "\nSome tests failed! ✗\n";
    exit(1);
}
