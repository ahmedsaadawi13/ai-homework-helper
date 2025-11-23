<?php
// FILE: /app/controllers/AiController.php

require_once __DIR__ . '/../helpers/AiEngine.php';

/**
 * AI Controller
 * Handles AI sessions, quizzes, and explanations
 */
class AiController extends Controller
{
    /**
     * List AI sessions
     */
    public function sessions()
    {
        $this->requireAuth();

        $aiSessionModel = $this->model('AiSession');

        $page = max(1, (int) $this->get('page', 1));
        $perPage = 20;

        $filters = [
            'session_type' => $this->get('session_type', ''),
        ];

        $sessions = $aiSessionModel->getWithFilters($filters, $perPage, ($page - 1) * $perPage);
        $totalSessions = $aiSessionModel->count();

        $paginator = new Paginator($totalSessions, $perPage, $page);

        $data = [
            'sessions' => $sessions,
            'paginator' => $paginator,
            'filters' => $filters,
        ];

        $this->view('ai/sessions', $data);
    }

    /**
     * Show generate quiz form
     */
    public function generateQuiz()
    {
        $this->requireAuth();

        $subjectModel = $this->model('Subject');
        $subjects = $subjectModel->getActive();

        $studentModel = $this->model('Student');
        $students = $studentModel->findAll(['status' => 'active'], 'name ASC');

        $data = [
            'subjects' => $subjects,
            'students' => $students,
        ];

        $this->view('ai/generate_quiz', $data);
    }

    /**
     * Generate quiz with AI
     */
    public function generateQuizPost()
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/ai/quiz/generate');
            return;
        }

        // Check AI request limit
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->hasReachedLimit(Auth::tenantId(), 'ai_requests')) {
            $this->flash('error', 'You have reached your AI request limit for this month. Please upgrade your plan.');
            $this->redirect('/admin/subscription');
            return;
        }

        $subjectId = $this->post('subject_id');
        $topic = $this->post('topic');
        $numQuestions = min(10, max(1, (int) $this->post('num_questions', 5)));
        $difficulty = $this->post('difficulty', 'medium');

        // Get subject
        $subjectModel = $this->model('Subject');
        $subject = $subjectModel->findById($subjectId);

        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/ai/quiz/generate');
            return;
        }

        // Generate quiz using AI
        $aiResult = AiEngine::generateQuiz($subject['name'], $topic, $numQuestions, $difficulty);

        // Create quiz
        $quizModel = $this->model('Quiz');
        $quizId = $quizModel->create([
            'subject_id' => $subjectId,
            'title' => $topic . ' - ' . ucfirst($difficulty) . ' Quiz',
            'description' => 'AI-generated quiz on ' . $topic,
            'difficulty' => $difficulty,
            'total_questions' => count($aiResult['questions']),
            'passing_score' => 70,
            'created_by' => Auth::id(),
        ]);

        if (!$quizId) {
            $this->flash('error', 'Failed to create quiz');
            $this->redirect('/ai/quiz/generate');
            return;
        }

        // Add questions
        foreach ($aiResult['questions'] as $question) {
            $quizModel->addQuestion($quizId, $question);
        }

        // Log AI session
        $aiSessionModel = $this->model('AiSession');
        $aiSessionModel->logSession([
            'user_id' => Auth::id(),
            'subject_id' => $subjectId,
            'session_type' => 'quiz',
            'prompt' => "Generate {$numQuestions} {$difficulty} questions on {$topic}",
            'response' => json_encode($aiResult['questions']),
            'tokens_used' => $aiResult['tokens_used'],
            'processing_time_ms' => $aiResult['processing_time_ms'],
        ]);

        $this->flash('success', 'Quiz generated successfully with ' . count($aiResult['questions']) . ' questions');
        $this->redirect('/ai/quiz/' . $quizId);
    }

    /**
     * Show quiz
     */
    public function showQuiz($id)
    {
        $this->requireAuth();

        $quizModel = $this->model('Quiz');
        $quiz = $quizModel->getWithDetails($id);

        if (!$quiz) {
            $this->flash('error', 'Quiz not found');
            $this->redirect('/ai/sessions');
            return;
        }

        $questions = $quizModel->getQuestions($id);

        $data = [
            'quiz' => $quiz,
            'questions' => $questions,
        ];

        $this->view('ai/quiz', $data);
    }

    /**
     * Submit quiz answers
     */
    public function submitQuiz($id)
    {
        $this->requireAuth();

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/ai/quiz/' . $id);
            return;
        }

        $quizModel = $this->model('Quiz');
        $quiz = $quizModel->findById($id);

        if (!$quiz) {
            $this->flash('error', 'Quiz not found');
            $this->redirect('/ai/sessions');
            return;
        }

        // Get student
        $studentModel = $this->model('Student');
        $student = $studentModel->findOne(['user_id' => Auth::id()]);

        if (!$student && !Auth::hasRole(['tenant_admin', 'teacher'])) {
            $this->flash('error', 'Student profile not found');
            $this->redirect('/ai/quiz/' . $id);
            return;
        }

        // For non-students, use a test student or skip result saving
        $studentId = $student ? $student['id'] : null;

        if (!$studentId) {
            $this->flash('info', 'Quiz completed (results not saved for non-student users)');
            $this->redirect('/ai/quiz/' . $id);
            return;
        }

        $questions = $quizModel->getQuestions($id);
        $answers = $this->post('answers', []);

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $totalPoints += $question['points'];

            $studentAnswer = $answers[$question['id']] ?? '';
            $correctAnswer = $question['correct_answer'];

            if (trim(strtolower($studentAnswer)) === trim(strtolower($correctAnswer))) {
                $earnedPoints += $question['points'];
            }
        }

        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $score >= $quiz['passing_score'];

        // Save result
        $startedAt = $this->post('started_at', date('Y-m-d H:i:s'));
        $timeTaken = time() - strtotime($startedAt);

        $quizModel->submitResult($id, $studentId, [
            'score' => $score,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'answers' => $answers,
            'started_at' => $startedAt,
            'time_taken_seconds' => $timeTaken,
            'passed' => $passed,
        ]);

        $message = $passed
            ? "Congratulations! You passed with a score of {$score}%"
            : "You scored {$score}%. Keep practicing!";

        $this->flash('success', $message);
        $this->redirect('/ai/quiz/' . $id);
    }
}
