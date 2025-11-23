<?php
// FILE: /app/controllers/HomeworkController.php

require_once __DIR__ . '/../helpers/AiEngine.php';

/**
 * Homework Controller
 * Handles homework help requests and AI responses
 */
class HomeworkController extends Controller
{
    /**
     * List all homework
     */
    public function index()
    {
        $this->requireAuth();

        $homeworkModel = $this->model('Homework');
        $subjectModel = $this->model('Subject');

        $page = max(1, (int) $this->get('page', 1));
        $perPage = 20;

        $filters = [
            'status' => $this->get('status', ''),
            'subject_id' => $this->get('subject_id', ''),
            'search' => $this->get('search', ''),
        ];

        // Filter by student for student role
        if (Auth::isStudent()) {
            $studentModel = $this->model('Student');
            $student = $studentModel->findOne(['user_id' => Auth::id()]);
            if ($student) {
                $filters['student_id'] = $student['id'];
            }
        }

        $homework = $homeworkModel->getWithFilters($filters, $perPage, ($page - 1) * $perPage);
        $totalHomework = $homeworkModel->countWithFilters($filters);

        $paginator = new Paginator($totalHomework, $perPage, $page);
        $subjects = $subjectModel->getActive();

        $data = [
            'homework' => $homework,
            'paginator' => $paginator,
            'subjects' => $subjects,
            'filters' => $filters,
        ];

        $this->view('homework/index', $data);
    }

    /**
     * Show homework details
     */
    public function show($id)
    {
        $this->requireAuth();

        $homeworkModel = $this->model('Homework');
        $homework = $homeworkModel->getWithDetails($id);

        if (!$homework) {
            $this->flash('error', 'Homework not found');
            $this->redirect('/homework');
            return;
        }

        // Get responses
        $responses = $homeworkModel->getResponses($id);

        $data = [
            'homework' => $homework,
            'responses' => $responses,
        ];

        $this->view('homework/show', $data);
    }

    /**
     * Show create homework form
     */
    public function create()
    {
        $this->requireAuth();

        $subjectModel = $this->model('Subject');
        $subjects = $subjectModel->getActive();

        $studentModel = $this->model('Student');

        // For students, get their own record
        if (Auth::isStudent()) {
            $student = $studentModel->findOne(['user_id' => Auth::id()]);
            $students = $student ? [$student] : [];
        } else {
            $students = $studentModel->findAll(['status' => 'active'], 'name ASC');
        }

        $data = [
            'subjects' => $subjects,
            'students' => $students,
        ];

        $this->view('homework/create', $data);
    }

    /**
     * Store new homework
     */
    public function store()
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/homework/create');
            return;
        }

        $studentId = $this->post('student_id');
        $subjectId = $this->post('subject_id');
        $title = $this->post('title');
        $description = $this->post('description');

        // Validate
        $validator = new Validator(compact('student_id', 'subject_id', 'title', 'description'));
        $validator->required('student_id')->numeric('student_id')
                  ->required('subject_id')->numeric('subject_id')
                  ->required('title')->min('title', 3)
                  ->required('description')->min('description', 10);

        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('/homework/create');
            return;
        }

        // Handle file upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new FileUpload();
            $image = $uploader->upload($_FILES['image'], 'homework');

            if (!$image) {
                $errors = $uploader->getErrors();
                $this->flash('error', $errors[0] ?? 'Failed to upload image');
                $this->redirect('/homework/create');
                return;
            }
        }

        $homeworkModel = $this->model('Homework');
        $homeworkId = $homeworkModel->create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'status' => 'new',
            'created_by' => Auth::id(),
        ]);

        if ($homeworkId) {
            $this->flash('success', 'Homework request created successfully');
            $this->redirect('/homework/' . $homeworkId);
        } else {
            $this->flash('error', 'Failed to create homework request');
            $this->redirect('/homework/create');
        }
    }

    /**
     * Request AI help for homework
     */
    public function askAi($id)
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/homework/' . $id);
            return;
        }

        // Check AI request limit
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'ai_requests')) {
            $this->flash('error', 'You have reached your AI request limit for this month. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $homeworkModel = $this->model('Homework');
        $homework = $homeworkModel->getWithDetails($id);

        if (!$homework) {
            $this->flash('error', 'Homework not found');
            $this->redirect('/homework');
            return;
        }

        // Get AI response
        $aiResult = AiEngine::generateHomeworkHelp(
            $homework['description'],
            $homework['subject_name'],
            $homework['grade_level'] ?? ''
        );

        // Save AI response
        $responseModel = $this->model('HomeworkResponse');
        $responseModel->addAiResponse($id, $aiResult['response']);

        // Log AI session
        $aiSessionModel = $this->model('AiSession');
        $aiSessionModel->logSession([
            'user_id' => Auth::id(),
            'student_id' => $homework['student_id'],
            'subject_id' => $homework['subject_id'],
            'session_type' => 'homework',
            'prompt' => $homework['description'],
            'response' => $aiResult['response']['content'],
            'tokens_used' => $aiResult['tokens_used'],
            'processing_time_ms' => $aiResult['processing_time_ms'],
        ]);

        // Update homework status
        $homeworkModel->update($id, ['status' => 'answered']);

        $this->flash('success', 'AI response generated successfully');
        $this->redirect('/homework/' . $id);
    }

    /**
     * Update homework status
     */
    public function updateStatus($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/homework/' . $id);
            return;
        }

        $status = $this->post('status');

        $homeworkModel = $this->model('Homework');

        if ($homeworkModel->update($id, ['status' => $status])) {
            $this->flash('success', 'Homework status updated');
        } else {
            $this->flash('error', 'Failed to update status');
        }

        $this->redirect('/homework/' . $id);
    }

    /**
     * Delete homework
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/homework');
            return;
        }

        $homeworkModel = $this->model('Homework');

        if ($homeworkModel->delete($id)) {
            $this->flash('success', 'Homework deleted successfully');
        } else {
            $this->flash('error', 'Failed to delete homework');
        }

        $this->redirect('/homework');
    }
}
