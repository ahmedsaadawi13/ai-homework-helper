<?php
// FILE: /app/core/Model.php

/**
 * Base Model class
 * All models should extend this class
 */
class Model
{
    protected $db;
    protected $table;
    protected $tenantScoped = true; // Most tables are tenant-scoped

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find all records with optional filters
     *
     * @param array $where Where conditions
     * @param string $orderBy Order by clause
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function findAll($where = [], $orderBy = 'id DESC', $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $where['tenant_id'] = $_SESSION['tenant_id'];
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);

        foreach ($where as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a record by ID
     *
     * @param int $id Record ID
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Find one record by conditions
     *
     * @param array $where Where conditions
     * @return array|false
     */
    public function findOne($where = [])
    {
        $sql = "SELECT * FROM {$this->table}";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $where['tenant_id'] = $_SESSION['tenant_id'];
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);

        foreach ($where as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create a new record
     *
     * @param array $data Record data
     * @return int|false Last insert ID or false
     */
    public function create($data)
    {
        // Add tenant_id if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $data['tenant_id'] = $_SESSION['tenant_id'];
        }

        // Add created_at timestamp
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $values = array_map(function ($field) {
            return ":{$field}";
        }, $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $values) . ")";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update a record
     *
     * @param int $id Record ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data)
    {
        // Add updated_at timestamp
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        }

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        return $stmt->execute();
    }

    /**
     * Delete a record
     *
     * @param int $id Record ID
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $stmt->bindValue(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Count records
     *
     * @param array $where Where conditions
     * @return int
     */
    public function count($where = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";

        // Add tenant scope if applicable
        if ($this->tenantScoped && isset($_SESSION['tenant_id'])) {
            $where['tenant_id'] = $_SESSION['tenant_id'];
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);

        foreach ($where as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Execute a custom query
     *
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    protected function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }
}
