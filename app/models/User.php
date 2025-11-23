<?php
// FILE: /app/models/User.php

/**
 * User model
 * Handles user data and authentication
 */
class User extends Model
{
    protected $table = 'users';
    protected $tenantScoped = false; // Users can be global (platform admin) or tenant-scoped

    /**
     * Find user by email
     *
     * @param string $email Email address
     * @return array|false
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return int|false
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return parent::create($data);
    }

    /**
     * Update user
     *
     * @param int $id User ID
     * @param array $data User data
     * @return bool
     */
    public function update($id, $data)
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return parent::update($id, $data);
    }

    /**
     * Get users by tenant
     *
     * @param int $tenantId Tenant ID
     * @param string $role Optional role filter
     * @return array
     */
    public function getByTenant($tenantId, $role = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id";

        if ($role) {
            $sql .= " AND role = :role";
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);

        if ($role) {
            $stmt->bindValue(':role', $role);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get teachers by tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getTeachers($tenantId)
    {
        return $this->getByTenant($tenantId, 'teacher');
    }

    /**
     * Get students by tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getStudents($tenantId)
    {
        return $this->getByTenant($tenantId, 'student');
    }

    /**
     * Check if email exists
     *
     * @param string $email Email
     * @param int $excludeId Exclude user ID (for updates)
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
