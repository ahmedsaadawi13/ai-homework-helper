<?php
// FILE: /app/models/Subject.php

/**
 * Subject model
 * Handles subject data
 */
class Subject extends Model
{
    protected $table = 'subjects';

    /**
     * Get subject with homework count
     *
     * @param int $id Subject ID
     * @return array|false
     */
    public function getWithStats($id)
    {
        $sql = "SELECT s.*,
                       COUNT(DISTINCT h.id) as homework_count,
                       COUNT(DISTINCT q.id) as quiz_count
                FROM {$this->table} s
                LEFT JOIN homework h ON s.id = h.subject_id
                LEFT JOIN quizzes q ON s.id = q.subject_id
                WHERE s.id = :id AND s.tenant_id = :tenant_id
                GROUP BY s.id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get all active subjects
     *
     * @return array
     */
    public function getActive()
    {
        return $this->findAll(['status' => 'active'], 'name ASC');
    }
}
