<?php
// FILE: /app/models/Tenant.php

/**
 * Tenant model
 * Handles tenant (education account) data
 */
class Tenant extends Model
{
    protected $table = 'tenants';
    protected $tenantScoped = false;

    /**
     * Get tenant with subscription info
     *
     * @param int $id Tenant ID
     * @return array|false
     */
    public function getWithSubscription($id)
    {
        $sql = "SELECT t.*, ts.plan_id, ts.status as subscription_status,
                       p.name as plan_name, p.max_students, p.max_classes,
                       p.max_ai_requests_per_month, p.max_storage_mb
                FROM {$this->table} t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id AND ts.status = 'active'
                LEFT JOIN plans p ON ts.plan_id = p.id
                WHERE t.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get current usage statistics for a tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getUsageStats($tenantId)
    {
        $stats = [];

        // Count students
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE tenant_id = :tenant_id AND status = 'active'");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['students_count'] = $result['count'];

        // Count classes
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM classes WHERE tenant_id = :tenant_id AND status = 'active'");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['classes_count'] = $result['count'];

        // Count AI requests this month
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM ai_sessions
                                    WHERE tenant_id = :tenant_id
                                    AND MONTH(created_at) = MONTH(CURRENT_DATE())
                                    AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['ai_requests_this_month'] = $result['count'];

        // Estimate storage usage (simplified)
        $stats['storage_mb'] = 0; // Would calculate based on file sizes

        return $stats;
    }

    /**
     * Check if tenant has reached limit
     *
     * @param int $tenantId Tenant ID
     * @param string $limitType Type of limit (students, classes, ai_requests, storage)
     * @return bool
     */
    public function hasReachedLimit($tenantId, $limitType)
    {
        $tenant = $this->getWithSubscription($tenantId);
        $usage = $this->getUsageStats($tenantId);

        switch ($limitType) {
            case 'students':
                return $usage['students_count'] >= $tenant['max_students'];
            case 'classes':
                return $usage['classes_count'] >= $tenant['max_classes'];
            case 'ai_requests':
                return $usage['ai_requests_this_month'] >= $tenant['max_ai_requests_per_month'];
            case 'storage':
                return $usage['storage_mb'] >= $tenant['max_storage_mb'];
            default:
                return false;
        }
    }

    /**
     * Generate unique API key
     *
     * @return string
     */
    public function generateApiKey()
    {
        return hash('sha256', uniqid('api_', true) . time());
    }
}
