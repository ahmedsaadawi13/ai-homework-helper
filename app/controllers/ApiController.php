<?php
// FILE: /app/controllers/ApiController.php

/**
 * API Controller
 * Handles public REST API for external integrations
 */
class ApiController extends Controller
{
    /**
     * Create homework help request via API
     * POST /api/v1/homework/create
     */
    public function createHomework()
    {
        // Validate API key
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key required'], 401);
            return;
        }

        // Find tenant by API key
        $tenantModel = $this->model('Tenant');
        $stmt = $tenantModel->query("SELECT * FROM tenants WHERE api_key = :api_key AND status = 'active'", [
            ':api_key' => $apiKey
        ]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
            return;
        }

        // Set tenant context
        $_SESSION['tenant_id'] = $tenant['id'];

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON input'], 400);
            return;
        }

        // Validate required fields
        $required = ['student_id', 'subject_id', 'title', 'description'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['error' => "Field '{$field}' is required"], 400);
                return;
            }
        }

        // Verify student belongs to tenant
        $studentModel = $this->model('Student');
        $student = $studentModel->findById($input['student_id']);

        if (!$student || $student['tenant_id'] != $tenant['id']) {
            $this->json(['error' => 'Invalid student_id'], 400);
            return;
        }

        // Verify subject belongs to tenant
        $subjectModel = $this->model('Subject');
        $subject = $subjectModel->findById($input['subject_id']);

        if (!$subject || $subject['tenant_id'] != $tenant['id']) {
            $this->json(['error' => 'Invalid subject_id'], 400);
            return;
        }

        // Create homework
        $homeworkModel = $this->model('Homework');
        $homeworkId = $homeworkModel->create([
            'student_id' => $input['student_id'],
            'subject_id' => $input['subject_id'],
            'title' => $input['title'],
            'description' => $input['description'],
            'status' => 'new',
        ]);

        if ($homeworkId) {
            $this->json([
                'success' => true,
                'homework_id' => $homeworkId,
                'message' => 'Homework help request created successfully',
            ], 201);
        } else {
            $this->json(['error' => 'Failed to create homework request'], 500);
        }
    }

    /**
     * Get homework details via API
     * GET /api/v1/homework/{id}
     */
    public function getHomework($id)
    {
        // Validate API key
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key required'], 401);
            return;
        }

        // Find tenant by API key
        $tenantModel = $this->model('Tenant');
        $stmt = $tenantModel->query("SELECT * FROM tenants WHERE api_key = :api_key AND status = 'active'", [
            ':api_key' => $apiKey
        ]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
            return;
        }

        // Set tenant context
        $_SESSION['tenant_id'] = $tenant['id'];

        // Get homework
        $homeworkModel = $this->model('Homework');
        $homework = $homeworkModel->getWithDetails($id);

        if (!$homework) {
            $this->json(['error' => 'Homework not found'], 404);
            return;
        }

        // Get responses
        $responses = $homeworkModel->getResponses($id);

        $this->json([
            'success' => true,
            'homework' => $homework,
            'responses' => $responses,
        ]);
    }
}
