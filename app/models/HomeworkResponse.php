<?php
// FILE: /app/models/HomeworkResponse.php

/**
 * HomeworkResponse model
 * Handles homework responses (AI answers and teacher comments)
 */
class HomeworkResponse extends Model
{
    protected $table = 'homework_responses';

    /**
     * Add AI response to homework
     *
     * @param int $homeworkId Homework ID
     * @param array $aiResponse AI response data
     * @return int|false
     */
    public function addAiResponse($homeworkId, $aiResponse)
    {
        $data = [
            'homework_id' => $homeworkId,
            'response_type' => 'ai',
            'content' => $aiResponse['content'],
            'step_by_step' => $aiResponse['step_by_step'] ?? null,
            'examples' => $aiResponse['examples'] ?? null,
            'related_topics' => $aiResponse['related_topics'] ?? null,
        ];

        return $this->create($data);
    }

    /**
     * Add teacher comment
     *
     * @param int $homeworkId Homework ID
     * @param int $userId User ID
     * @param string $content Comment content
     * @return int|false
     */
    public function addTeacherComment($homeworkId, $userId, $content)
    {
        $data = [
            'homework_id' => $homeworkId,
            'response_type' => 'teacher',
            'user_id' => $userId,
            'content' => $content,
        ];

        return $this->create($data);
    }
}
