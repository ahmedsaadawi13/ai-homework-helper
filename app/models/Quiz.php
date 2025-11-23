<?php
// FILE: /app/models/Quiz.php

/**
 * Quiz model
 * Handles quiz data
 */
class Quiz extends Model
{
    protected $table = 'quizzes';

    /**
     * Get quiz with details
     *
     * @param int $id Quiz ID
     * @return array|false
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT q.*,
                       sub.name as subject_name,
                       s.name as student_name,
                       c.name as class_name
                FROM {$this->table} q
                LEFT JOIN subjects sub ON q.subject_id = sub.id
                LEFT JOIN students s ON q.student_id = s.id
                LEFT JOIN classes c ON q.class_id = c.id
                WHERE q.id = :id AND q.tenant_id = :tenant_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get quiz questions
     *
     * @param int $quizId Quiz ID
     * @return array
     */
    public function getQuestions($quizId)
    {
        $sql = "SELECT * FROM quiz_questions
                WHERE quiz_id = :quiz_id
                ORDER BY order_num ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add question to quiz
     *
     * @param int $quizId Quiz ID
     * @param array $questionData Question data
     * @return int|false
     */
    public function addQuestion($quizId, $questionData)
    {
        $data = [
            'quiz_id' => $quizId,
            'question_type' => $questionData['question_type'],
            'question_text' => $questionData['question_text'],
            'options' => $questionData['options'] ?? null,
            'correct_answer' => $questionData['correct_answer'],
            'explanation' => $questionData['explanation'] ?? null,
            'points' => $questionData['points'] ?? 1,
            'order_num' => $questionData['order_num'] ?? 0,
        ];

        $sql = "INSERT INTO quiz_questions (tenant_id, quiz_id, question_type, question_text, options, correct_answer, explanation, points, order_num)
                VALUES (:tenant_id, :quiz_id, :question_type, :question_text, :options, :correct_answer, :explanation, :points, :order_num)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':quiz_id', $data['quiz_id'], PDO::PARAM_INT);
        $stmt->bindValue(':question_type', $data['question_type']);
        $stmt->bindValue(':question_text', $data['question_text']);
        $stmt->bindValue(':options', $data['options']);
        $stmt->bindValue(':correct_answer', $data['correct_answer']);
        $stmt->bindValue(':explanation', $data['explanation']);
        $stmt->bindValue(':points', $data['points'], PDO::PARAM_INT);
        $stmt->bindValue(':order_num', $data['order_num'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Submit quiz result
     *
     * @param int $quizId Quiz ID
     * @param int $studentId Student ID
     * @param array $resultData Result data
     * @return int|false
     */
    public function submitResult($quizId, $studentId, $resultData)
    {
        $data = [
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'score' => $resultData['score'],
            'total_points' => $resultData['total_points'],
            'earned_points' => $resultData['earned_points'],
            'answers' => json_encode($resultData['answers']),
            'started_at' => $resultData['started_at'],
            'completed_at' => date('Y-m-d H:i:s'),
            'time_taken_seconds' => $resultData['time_taken_seconds'],
            'passed' => $resultData['passed'] ? 1 : 0,
        ];

        $sql = "INSERT INTO quiz_results (tenant_id, quiz_id, student_id, score, total_points, earned_points, answers, started_at, completed_at, time_taken_seconds, passed)
                VALUES (:tenant_id, :quiz_id, :student_id, :score, :total_points, :earned_points, :answers, :started_at, :completed_at, :time_taken_seconds, :passed)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':quiz_id', $data['quiz_id'], PDO::PARAM_INT);
        $stmt->bindValue(':student_id', $data['student_id'], PDO::PARAM_INT);
        $stmt->bindValue(':score', $data['score']);
        $stmt->bindValue(':total_points', $data['total_points'], PDO::PARAM_INT);
        $stmt->bindValue(':earned_points', $data['earned_points'], PDO::PARAM_INT);
        $stmt->bindValue(':answers', $data['answers']);
        $stmt->bindValue(':started_at', $data['started_at']);
        $stmt->bindValue(':completed_at', $data['completed_at']);
        $stmt->bindValue(':time_taken_seconds', $data['time_taken_seconds'], PDO::PARAM_INT);
        $stmt->bindValue(':passed', $data['passed'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get student quiz results
     *
     * @param int $studentId Student ID
     * @return array
     */
    public function getStudentResults($studentId)
    {
        $sql = "SELECT qr.*, q.title as quiz_title, sub.name as subject_name
                FROM quiz_results qr
                INNER JOIN quizzes q ON qr.quiz_id = q.id
                INNER JOIN subjects sub ON q.subject_id = sub.id
                WHERE qr.student_id = :student_id AND qr.tenant_id = :tenant_id
                ORDER BY qr.completed_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
