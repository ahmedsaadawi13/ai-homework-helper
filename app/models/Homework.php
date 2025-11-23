<?php
// FILE: /app/models/Homework.php

/**
 * Homework model
 * Handles homework help requests
 */
class Homework extends Model
{
    protected $table = 'homework';

    /**
     * Get homework with related data
     *
     * @param int $id Homework ID
     * @return array|false
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT h.*,
                       s.name as student_name,
                       sub.name as subject_name,
                       c.name as class_name,
                       u.name as created_by_name
                FROM {$this->table} h
                LEFT JOIN students s ON h.student_id = s.id
                LEFT JOIN subjects sub ON h.subject_id = sub.id
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = :id AND h.tenant_id = :tenant_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get homework list with pagination and filters
     *
     * @param array $filters Filters
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getWithFilters($filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT h.*,
                       s.name as student_name,
                       sub.name as subject_name,
                       c.name as class_name
                FROM {$this->table} h
                LEFT JOIN students s ON h.student_id = s.id
                LEFT JOIN subjects sub ON h.subject_id = sub.id
                LEFT JOIN classes c ON h.class_id = c.id
                WHERE h.tenant_id = :tenant_id";

        $params = [':tenant_id' => $_SESSION['tenant_id']];

        if (!empty($filters['status'])) {
            $sql .= " AND h.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND h.subject_id = :subject_id";
            $params[':subject_id'] = $filters['subject_id'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND h.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (h.title LIKE :search OR h.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY h.created_at DESC LIMIT :limit OFFSET :offset";

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
     * Get homework responses
     *
     * @param int $homeworkId Homework ID
     * @return array
     */
    public function getResponses($homeworkId)
    {
        $sql = "SELECT hr.*, u.name as user_name
                FROM homework_responses hr
                LEFT JOIN users u ON hr.user_id = u.id
                WHERE hr.homework_id = :homework_id
                ORDER BY hr.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':homework_id', $homeworkId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count by filters
     *
     * @param array $filters Filters
     * @return int
     */
    public function countWithFilters($filters = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tenant_id = :tenant_id";

        $params = [':tenant_id' => $_SESSION['tenant_id']];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND subject_id = :subject_id";
            $params[':subject_id'] = $filters['subject_id'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
}
