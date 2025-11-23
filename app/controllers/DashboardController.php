<?php
// FILE: /app/controllers/DashboardController.php

/**
 * Dashboard Controller
 * Handles main dashboard for all user roles
 */
class DashboardController extends Controller
{
    /**
     * Dashboard index
     */
    public function index()
    {
        $this->requireAuth();

        $role = Auth::role();

        // Route to appropriate dashboard based on role
        switch ($role) {
            case 'platform_admin':
                $this->platformAdminDashboard();
                break;
            case 'tenant_admin':
                $this->tenantAdminDashboard();
                break;
            case 'teacher':
                $this->teacherDashboard();
                break;
            case 'student':
                $this->studentDashboard();
                break;
            default:
                $this->flash('error', 'Invalid user role');
                $this->redirect('/logout');
        }
    }

    /**
     * Platform admin dashboard
     */
    private function platformAdminDashboard()
    {
        $tenantModel = $this->model('Tenant');
        $userModel = $this->model('User');

        $data = [
            'total_tenants' => $tenantModel->count(),
            'active_tenants' => $tenantModel->count(['status' => 'active']),
            'recent_tenants' => $tenantModel->findAll([], 'created_at DESC', 5),
        ];

        $this->view('dashboard/platform_admin', $data);
    }

    /**
     * Tenant admin dashboard
     */
    private function tenantAdminDashboard()
    {
        $tenantId = Auth::tenantId();

        $studentModel = $this->model('Student');
        $homeworkModel = $this->model('Homework');
        $aiSessionModel = $this->model('AiSession');
        $tenantModel = $this->model('Tenant');

        // Get usage stats
        $tenant = $tenantModel->getWithSubscription($tenantId);
        $usage = $tenantModel->getUsageStats($tenantId);

        // Get recent activity
        $recentHomework = $homeworkModel->findAll([], 'created_at DESC', 5);
        $recentSessions = $aiSessionModel->findAll([], 'created_at DESC', 5);

        // Get homework stats by status
        $homeworkStats = [
            'new' => $homeworkModel->count(['status' => 'new']),
            'in_progress' => $homeworkModel->count(['status' => 'in_progress']),
            'answered' => $homeworkModel->count(['status' => 'answered']),
            'total' => $homeworkModel->count(),
        ];

        $data = [
            'tenant' => $tenant,
            'usage' => $usage,
            'students_count' => $studentModel->count(['status' => 'active']),
            'homework_stats' => $homeworkStats,
            'recent_homework' => $recentHomework,
            'recent_sessions' => $recentSessions,
        ];

        $this->view('dashboard/tenant_admin', $data);
    }

    /**
     * Teacher dashboard
     */
    private function teacherDashboard()
    {
        $homeworkModel = $this->model('Homework');
        $studentModel = $this->model('Student');

        // Get recent homework
        $recentHomework = $homeworkModel->findAll([], 'created_at DESC', 10);

        // Get homework requiring attention
        $pendingHomework = $homeworkModel->findAll(['status' => 'new'], 'created_at DESC', 5);

        $data = [
            'recent_homework' => $recentHomework,
            'pending_homework' => $pendingHomework,
            'total_students' => $studentModel->count(['status' => 'active']),
        ];

        $this->view('dashboard/teacher', $data);
    }

    /**
     * Student dashboard
     */
    private function studentDashboard()
    {
        $userId = Auth::id();
        $studentModel = $this->model('Student');
        $homeworkModel = $this->model('Homework');
        $quizModel = $this->model('Quiz');

        // Get student record
        $student = $studentModel->findOne(['user_id' => $userId]);

        if (!$student) {
            $this->flash('error', 'Student profile not found');
            $this->redirect('/logout');
            return;
        }

        // Get student's homework
        $myHomework = $homeworkModel->findAll(['student_id' => $student['id']], 'created_at DESC', 10);

        // Get performance stats
        $stats = $studentModel->getPerformanceStats($student['id']);

        // Get recent quiz results
        $quizResults = $quizModel->getStudentResults($student['id']);

        $data = [
            'student' => $student,
            'my_homework' => $myHomework,
            'stats' => $stats,
            'quiz_results' => $quizResults,
        ];

        $this->view('dashboard/student', $data);
    }
}
