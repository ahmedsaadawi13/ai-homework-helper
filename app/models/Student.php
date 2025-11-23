<?php
// FILE: /app/models/Student.php

/**
 * Student model
 * Handles student data
 */
class Student extends Model
{
    protected $table = 'students';

    /**
     * Get student with class and user info
     *
     * @param int $id Student ID
     * @return array|false
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT s.*, c.name as class_name, c.grade_level,
                       u.email as user_email
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = :id";

        if (isset($_SESSION['tenant_id'])) {
            $sql .= " AND s.tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (isset($_SESSION['tenant_id'])) {
            $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get students with pagination and search
     *
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $search Search term
     * @return array
     */
    public function getWithPagination($limit = 20, $offset = 0, $search = '')
    {
        $sql = "SELECT s.*, c.name as class_name
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.tenant_id = :tenant_id";

        if ($search) {
            $sql .= " AND (s.name LIKE :search OR s.email LIKE :search OR s.parent_name LIKE :search)";
        }

        $sql .= " ORDER BY s.name ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get student performance stats
     *
     * @param int $studentId Student ID
     * @return array
     */
    public function getPerformanceStats($studentId)
    {
        $stats = [];

        // Homework count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM homework WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['homework_count'] = $result['count'];

        // Quizzes taken
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM quiz_results WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['quizzes_taken'] = $result['count'];

        // Average quiz score
        $stmt = $this->db->prepare("SELECT AVG(score) as avg_score FROM quiz_results WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['average_score'] = round($result['avg_score'] ?? 0, 2);

        // AI sessions count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM ai_sessions WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['ai_sessions'] = $result['count'];

        return $stats;
    }

    /**
     * Get students by class
     *
     * @param int $classId Class ID
     * @return array
     */
    public function getByClass($classId)
    {
        return $this->findAll(['class_id' => $classId, 'status' => 'active'], 'name ASC');
    }
}
