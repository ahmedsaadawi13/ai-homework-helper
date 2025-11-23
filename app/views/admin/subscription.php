<!-- FILE: /app/views/admin/subscription.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Subscription Management</h1>

    <!-- Current Plan -->
    <div class="card">
        <h2>Current Plan: <?php echo htmlspecialchars($tenant['plan_name'] ?? 'No Plan'); ?></h2>
        <p>Status: <span class="badge badge-<?php echo $tenant['subscription_status'] === 'active' ? 'success' : 'warning'; ?>">
            <?php echo htmlspecialchars($tenant['subscription_status'] ?? 'N/A'); ?>
        </span></p>
        <a href="/admin/plans" class="btn btn-primary">Change Plan</a>
    </div>

    <!-- Usage Statistics -->
    <div class="card">
        <h2>Current Usage</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Resource</th>
                    <th>Current Usage</th>
                    <th>Plan Limit</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Students</td>
                    <td><?php echo $usage['students_count']; ?></td>
                    <td><?php echo $tenant['max_students'] ?? 'Unlimited'; ?></td>
                    <td>
                        <?php
                        $pct = $tenant['max_students'] > 0 ? round(($usage['students_count'] / $tenant['max_students']) * 100, 2) : 0;
                        echo $pct . '%';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Classes</td>
                    <td><?php echo $usage['classes_count']; ?></td>
                    <td><?php echo $tenant['max_classes'] ?? 'Unlimited'; ?></td>
                    <td>
                        <?php
                        $pct = $tenant['max_classes'] > 0 ? round(($usage['classes_count'] / $tenant['max_classes']) * 100, 2) : 0;
                        echo $pct . '%';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>AI Requests (This Month)</td>
                    <td><?php echo $usage['ai_requests_this_month']; ?></td>
                    <td><?php echo $tenant['max_ai_requests_per_month'] ?? 'Unlimited'; ?></td>
                    <td>
                        <?php
                        $pct = $tenant['max_ai_requests_per_month'] > 0 ? round(($usage['ai_requests_this_month'] / $tenant['max_ai_requests_per_month']) * 100, 2) : 0;
                        echo $pct . '%';
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if ($pct >= 90): ?>
            <div class="alert alert-warning">
                You are approaching your plan limits. Consider upgrading to continue uninterrupted service.
            </div>
        <?php endif; ?>
    </div>

    <!-- Billing -->
    <div class="card">
        <h2>Billing</h2>
        <a href="/admin/billing" class="btn btn-secondary">View Billing History</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
