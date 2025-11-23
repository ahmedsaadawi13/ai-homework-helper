<!-- FILE: /app/views/admin/plans.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Subscription Plans</h1>

    <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
            <div class="plan-card <?php echo $plan['id'] == $current_plan_id ? 'current-plan' : ''; ?>">
                <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                <p class="plan-price">$<?php echo number_format($plan['price_monthly'], 2); ?> / month</p>
                <p><?php echo htmlspecialchars($plan['description']); ?></p>

                <ul class="plan-features">
                    <li>Up to <?php echo number_format($plan['max_students']); ?> students</li>
                    <li>Up to <?php echo number_format($plan['max_classes']); ?> classes</li>
                    <li><?php echo number_format($plan['max_ai_requests_per_month']); ?> AI requests/month</li>
                    <li><?php echo number_format($plan['max_storage_mb']); ?> MB storage</li>
                </ul>

                <?php if ($plan['id'] == $current_plan_id): ?>
                    <button class="btn btn-secondary" disabled>Current Plan</button>
                <?php else: ?>
                    <form method="POST" action="/admin/subscription/change">
                        <?php echo View::csrfField(); ?>
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <button type="submit" class="btn btn-primary">Select Plan</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
