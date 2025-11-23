<?php
// FILE: /app/helpers/Validator.php

/**
 * Validation helper class
 * Provides common validation methods
 */
class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     *
     * @param array $data Data to validate
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Validate required field
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }

    /**
     * Validate email
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email address';
        }
        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param int $min Minimum length
     * @param string $message Error message
     * @return self
     */
    public function min($field, $min, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min} characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param int $max Maximum length
     * @param string $message Error message
     * @return self
     */
    public function max($field, $max, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max} characters";
        }
        return $this;
    }

    /**
     * Validate numeric
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function numeric($field, $message = null)
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be a number';
        }
        return $this;
    }

    /**
     * Validate match with another field
     *
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Error message
     * @return self
     */
    public function match($field, $matchField, $message = null)
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) &&
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' does not match';
        }
        return $this;
    }

    /**
     * Validate unique in database
     *
     * @param string $field Field name
     * @param string $table Table name
     * @param int $excludeId ID to exclude (for updates)
     * @param string $message Error message
     * @return self
     */
    public function unique($field, $table, $excludeId = null, $message = null)
    {
        if (!isset($this->data[$field])) {
            return $this;
        }

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = :value";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $this->data[$field]);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' already exists';
        }

        return $this;
    }

    /**
     * Check if validation passes
     *
     * @return bool
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation fails
     *
     * @return bool
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get first error
     *
     * @return string|null
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
