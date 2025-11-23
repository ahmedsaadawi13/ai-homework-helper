<?php
// FILE: /app/models/Plan.php

/**
 * Plan model
 * Handles subscription plan data
 */
class Plan extends Model
{
    protected $table = 'plans';
    protected $tenantScoped = false;

    /**
     * Get all active plans
     *
     * @return array
     */
    public function getActive()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY price_monthly ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
