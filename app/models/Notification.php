<?php
// FILE: /app/models/Notification.php

/**
 * Notification model
 * Handles user notifications
 */
class Notification extends Model
{
    protected $table = 'notifications';

    /**
     * Get unread notifications for user
     *
     * @param int $userId User ID
     * @param int $limit Limit
     * @return array
     */
    public function getUnread($userId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id AND is_read = 0
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark notification as read
     *
     * @param int $id Notification ID
     * @return bool
     */
    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => 1]);
    }

    /**
     * Mark all as read for user
     *
     * @param int $userId User ID
     * @return bool
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE {$this->table} SET is_read = 1
                WHERE user_id = :user_id AND tenant_id = :tenant_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Create notification
     *
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $title Title
     * @param string $message Message
     * @param string $link Optional link
     * @return int|false
     */
    public function notify($userId, $type, $title, $message, $link = null)
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ];

        return $this->create($data);
    }
}
