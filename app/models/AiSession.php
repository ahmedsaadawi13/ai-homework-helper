<?php
// FILE: /app/models/AiSession.php

/**
 * AiSession model
 * Handles AI interaction tracking
 */
class AiSession extends Model
{
    protected $table = 'ai_sessions';

    /**
     * Log AI session
     *
     * @param array $data Session data
     * @return int|false
     */
    public function logSession($data)
    {
        $sessionData = [
            'user_id' => $data['user_id'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'session_type' => $data['session_type'],
            'prompt' => $data['prompt'],
            'response' => $data['response'],
            'tokens_used' => $data['tokens_used'] ?? 0,
            'processing_time_ms' => $data['processing_time_ms'] ?? 0,
        ];

        return $this->create($sessionData);
    }

    /**
     * Get sessions with filters
     *
     * @param array $filters Filters
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getWithFilters($filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT a.*,
                       u.name as user_name,
                       s.name as student_name,
                       sub.name as subject_name
                FROM {$this->table} a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN subjects sub ON a.subject_id = sub.id
                WHERE a.tenant_id = :tenant_id";

        $params = [':tenant_id' => $_SESSION['tenant_id']];

        if (!empty($filters['session_type'])) {
            $sql .= " AND a.session_type = :session_type";
            $params[':session_type'] = $filters['session_type'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND a.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get monthly AI usage count
     *
     * @param int $tenantId Tenant ID
     * @param int $month Month
     * @param int $year Year
     * @return int
     */
    public function getMonthlyCount($tenantId, $month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE tenant_id = :tenant_id
                AND MONTH(created_at) = :month
                AND YEAR(created_at) = :year";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) $result['count'];
    }
}
