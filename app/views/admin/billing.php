<!-- FILE: /app/views/admin/billing.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Billing History</h1>

    <!-- Invoices -->
    <div class="card">
        <h2>Invoices</h2>
        <?php if (!empty($invoices)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Paid Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                            <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                            <td><span class="badge badge-<?php echo $invoice['status']; ?>"><?php echo $invoice['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                            <td><?php echo $invoice['paid_at'] ? date('M d, Y', strtotime($invoice['paid_at'])) : '-'; ?></td>
                            <td>
                                <?php if ($invoice['status'] === 'pending'): ?>
                                    <form method="POST" action="/admin/payment/simulate">
                                        <?php echo View::csrfField(); ?>
                                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Pay Now (Simulate)</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No invoices found.</p>
        <?php endif; ?>
    </div>

    <!-- Payments -->
    <div class="card">
        <h2>Payment History</h2>
        <?php if (!empty($payments)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['invoice_number']); ?></td>
                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo $payment['status']; ?>"><?php echo $payment['status']; ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payments found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
