<?php
// FILE: /app/controllers/ReportController.php

/**
 * Report Controller
 * Handles reporting and analytics
 */
class ReportController extends Controller
{
    /**
     * Homework report
     */
    public function homework()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $homeworkModel = $this->model('Homework');

        // Get date range
        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-d'));

        // Get homework stats
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM homework
                WHERE tenant_id = :tenant_id
                AND DATE(created_at) BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        $stmt = $homeworkModel->query($sql, [
            ':tenant_id' => Auth::tenantId(),
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        $dailyStats = $stmt->fetchAll();

        // Get stats by subject
        $sql = "SELECT s.name as subject, COUNT(h.id) as count
                FROM homework h
                INNER JOIN subjects s ON h.subject_id = s.id
                WHERE h.tenant_id = :tenant_id
                AND DATE(h.created_at) BETWEEN :start_date AND :end_date
                GROUP BY s.id
                ORDER BY count DESC";

        $stmt = $homeworkModel->query($sql, [
            ':tenant_id' => Auth::tenantId(),
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        $subjectStats = $stmt->fetchAll();

        $data = [
            'daily_stats' => $dailyStats,
            'subject_stats' => $subjectStats,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $this->view('reports/homework', $data);
    }

    /**
     * Performance report
     */
    public function performance()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'teacher']);

        $studentModel = $this->model('Student');
        $students = $studentModel->findAll(['status' => 'active'], 'name ASC', 20);

        // Get performance stats for each student
        $performanceData = [];
        foreach ($students as $student) {
            $stats = $studentModel->getPerformanceStats($student['id']);
            $performanceData[] = array_merge($student, $stats);
        }

        $data = ['students' => $performanceData];

        $this->view('reports/performance', $data);
    }

    /**
     * Usage report
     */
    public function usage()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->getWithSubscription(Auth::tenantId());
        $usage = $tenantModel->getUsageStats(Auth::tenantId());

        // Calculate usage percentages
        $usagePercentages = [
            'students' => $tenant['max_students'] > 0 ? round(($usage['students_count'] / $tenant['max_students']) * 100, 2) : 0,
            'classes' => $tenant['max_classes'] > 0 ? round(($usage['classes_count'] / $tenant['max_classes']) * 100, 2) : 0,
            'ai_requests' => $tenant['max_ai_requests_per_month'] > 0 ? round(($usage['ai_requests_this_month'] / $tenant['max_ai_requests_per_month']) * 100, 2) : 0,
        ];

        $data = [
            'tenant' => $tenant,
            'usage' => $usage,
            'usage_percentages' => $usagePercentages,
        ];

        $this->view('reports/usage', $data);
    }
}
