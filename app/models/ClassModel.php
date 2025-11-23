<?php
// FILE: /app/models/ClassModel.php

/**
 * ClassModel
 * Handles class/group data
 * Named ClassModel to avoid PHP keyword conflict
 */
class ClassModel extends Model
{
    protected $table = 'classes';

    /**
     * Get class with student count
     *
     * @param int $id Class ID
     * @return array|false
     */
    public function getWithStudentCount($id)
    {
        $sql = "SELECT c.*, COUNT(s.id) as student_count
                FROM {$this->table} c
                LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                WHERE c.id = :id AND c.tenant_id = :tenant_id
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get all classes with student counts
     *
     * @return array
     */
    public function getAllWithCounts()
    {
        $sql = "SELECT c.*, COUNT(s.id) as student_count
                FROM {$this->table} c
                LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                WHERE c.tenant_id = :tenant_id
                GROUP BY c.id
                ORDER BY c.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get class subjects
     *
     * @param int $classId Class ID
     * @return array
     */
    public function getSubjects($classId)
    {
        $sql = "SELECT s.*
                FROM subjects s
                INNER JOIN class_subject cs ON s.id = cs.subject_id
                WHERE cs.class_id = :class_id AND cs.tenant_id = :tenant_id
                ORDER BY s.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Assign subject to class
     *
     * @param int $classId Class ID
     * @param int $subjectId Subject ID
     * @return bool
     */
    public function assignSubject($classId, $subjectId)
    {
        $sql = "INSERT INTO class_subject (tenant_id, class_id, subject_id)
                VALUES (:tenant_id, :class_id, :subject_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Already assigned
            return false;
        }
    }

    /**
     * Remove subject from class
     *
     * @param int $classId Class ID
     * @param int $subjectId Subject ID
     * @return bool
     */
    public function removeSubject($classId, $subjectId)
    {
        $sql = "DELETE FROM class_subject
                WHERE class_id = :class_id AND subject_id = :subject_id AND tenant_id = :tenant_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $stmt->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
