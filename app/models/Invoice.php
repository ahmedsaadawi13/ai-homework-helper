<?php
// FILE: /app/models/Invoice.php

/**
 * Invoice model
 * Handles invoice data
 */
class Invoice extends Model
{
    protected $table = 'invoices';
    protected $tenantScoped = false; // Can be accessed by platform admin

    /**
     * Get invoices by tenant
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generate unique invoice number
     *
     * @return string
     */
    public function generateInvoiceNumber()
    {
        return 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create invoice
     *
     * @param array $data Invoice data
     * @return int|false
     */
    public function create($data)
    {
        if (!isset($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        return parent::create($data);
    }
}
