<?php
// FILE: /app/models/Payment.php

/**
 * Payment model
 * Handles payment data
 */
class Payment extends Model
{
    protected $table = 'payments';
    protected $tenantScoped = false;

    /**
     * Get payments by tenant
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getByTenant($tenantId)
    {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                INNER JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = :tenant_id
                ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generate transaction ID
     *
     * @return string
     */
    public function generateTransactionId()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
