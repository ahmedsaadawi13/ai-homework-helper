<?php
// FILE: /app/models/TenantSubscription.php

/**
 * TenantSubscription model
 * Handles tenant subscription data
 */
class TenantSubscription extends Model
{
    protected $table = 'tenant_subscriptions';
    protected $tenantScoped = false;

    /**
     * Get active subscription for tenant
     *
     * @param int $tenantId Tenant ID
     * @return array|false
     */
    public function getActive($tenantId)
    {
        $sql = "SELECT ts.*, p.name as plan_name, p.price_monthly,
                       p.max_students, p.max_classes, p.max_ai_requests_per_month, p.max_storage_mb
                FROM {$this->table} ts
                INNER JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = :tenant_id AND ts.status = 'active'
                ORDER BY ts.id DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Change subscription plan
     *
     * @param int $tenantId Tenant ID
     * @param int $newPlanId New plan ID
     * @return int|false
     */
    public function changePlan($tenantId, $newPlanId)
    {
        // Cancel current subscription
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE tenant_id = :tenant_id AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        // Create new subscription
        $data = [
            'tenant_id' => $tenantId,
            'plan_id' => $newPlanId,
            'status' => 'active',
            'started_at' => date('Y-m-d H:i:s'),
        ];

        $sql = "INSERT INTO {$this->table} (tenant_id, plan_id, status, started_at)
                VALUES (:tenant_id, :plan_id, :status, :started_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':plan_id', $data['plan_id'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':started_at', $data['started_at']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }
}
