<?php
// FILE: /app/controllers/SubscriptionController.php

/**
 * Subscription Controller
 * Handles subscription and billing management
 */
class SubscriptionController extends Controller
{
    /**
     * Show subscription overview
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = Auth::tenantId();

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->getWithSubscription($tenantId);
        $usage = $tenantModel->getUsageStats($tenantId);

        $data = [
            'tenant' => $tenant,
            'usage' => $usage,
        ];

        $this->view('admin/subscription', $data);
    }

    /**
     * Show available plans
     */
    public function plans()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $planModel = $this->model('Plan');
        $plans = $planModel->getActive();

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->getWithSubscription(Auth::tenantId());

        $data = [
            'plans' => $plans,
            'current_plan_id' => $tenant['plan_id'] ?? null,
        ];

        $this->view('admin/plans', $data);
    }

    /**
     * Change subscription plan
     */
    public function changePlan()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/plans');
            return;
        }

        $planId = $this->post('plan_id');

        $subscriptionModel = $this->model('TenantSubscription');
        $result = $subscriptionModel->changePlan(Auth::tenantId(), $planId);

        if ($result) {
            $this->flash('success', 'Your subscription plan has been updated successfully');
            $this->redirect('/admin/subscription');
        } else {
            $this->flash('error', 'Failed to update subscription plan');
            $this->redirect('/admin/plans');
        }
    }

    /**
     * Show billing history
     */
    public function billing()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = Auth::tenantId();

        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');

        $invoices = $invoiceModel->getByTenant($tenantId);
        $payments = $paymentModel->getByTenant($tenantId);

        $data = [
            'invoices' => $invoices,
            'payments' => $payments,
        ];

        $this->view('admin/billing', $data);
    }

    /**
     * Simulate payment (dummy payment gateway)
     */
    public function simulatePayment()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if (!$this->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Invalid request');
            $this->redirect('/admin/billing');
            return;
        }

        $invoiceId = $this->post('invoice_id');

        $invoiceModel = $this->model('Invoice');
        $invoice = $invoiceModel->findById($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != Auth::tenantId()) {
            $this->flash('error', 'Invoice not found');
            $this->redirect('/admin/billing');
            return;
        }

        if ($invoice['status'] === 'paid') {
            $this->flash('info', 'Invoice already paid');
            $this->redirect('/admin/billing');
            return;
        }

        // Simulate payment
        $paymentModel = $this->model('Payment');
        $transactionId = $paymentModel->generateTransactionId();

        $paymentId = $paymentModel->create([
            'tenant_id' => Auth::tenantId(),
            'invoice_id' => $invoiceId,
            'amount' => $invoice['total'],
            'payment_method' => 'credit_card',
            'transaction_id' => $transactionId,
            'status' => 'completed',
        ]);

        if ($paymentId) {
            // Update invoice
            $invoiceModel->update($invoiceId, [
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            $this->flash('success', 'Payment processed successfully. Transaction ID: ' . $transactionId);
        } else {
            $this->flash('error', 'Payment failed');
        }

        $this->redirect('/admin/billing');
    }
}
