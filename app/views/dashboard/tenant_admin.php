<!-- FILE: /app/views/dashboard/tenant_admin.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Tenant Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars(Auth::user()['name']); ?>!</p>

    <!-- Usage Stats -->
    <div class="card-grid">
        <div class="stat-card">
            <h3><?php echo $students_count; ?></h3>
            <p>Active Students</p>
            <small><?php echo $usage['students_count']; ?> / <?php echo $tenant['max_students'] ?? 'Unlimited'; ?> used</small>
        </div>
        <div class="stat-card">
            <h3><?php echo $homework_stats['total']; ?></h3>
            <p>Total Homework</p>
            <small><?php echo $homework_stats['new']; ?> new, <?php echo $homework_stats['answered']; ?> answered</small>
        </div>
        <div class="stat-card">
            <h3><?php echo $usage['ai_requests_this_month']; ?></h3>
            <p>AI Requests This Month</p>
            <small><?php echo $usage['ai_requests_this_month']; ?> / <?php echo $tenant['max_ai_requests_per_month'] ?? 'Unlimited'; ?></small>
        </div>
        <div class="stat-card">
            <h3><?php echo $usage['classes_count']; ?></h3>
            <p>Active Classes</p>
            <small><?php echo $usage['classes_count']; ?> / <?php echo $tenant['max_classes'] ?? 'Unlimited'; ?> used</small>
        </div>
    </div>

    <!-- Subscription Plan -->
    <div class="card">
        <h2>Current Plan: <?php echo htmlspecialchars($tenant['plan_name'] ?? 'No Plan'); ?></h2>
        <p>Status: <span class="badge badge-<?php echo $tenant['subscription_status'] === 'active' ? 'success' : 'warning'; ?>">
            <?php echo htmlspecialchars($tenant['subscription_status'] ?? 'N/A'); ?>
        </span></p>
        <a href="/admin/subscription" class="btn btn-secondary">Manage Subscription</a>
    </div>

    <!-- Recent Homework -->
    <div class="card">
        <h2>Recent Homework Requests</h2>
        <?php if (!empty($recent_homework)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_homework as $hw): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hw['title']); ?></td>
                            <td><?php echo htmlspecialchars($hw['student_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($hw['subject_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-<?php echo $hw['status']; ?>"><?php echo $hw['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($hw['created_at'])); ?></td>
                            <td><a href="/homework/<?php echo $hw['id']; ?>" class="btn btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No homework requests yet.</p>
        <?php endif; ?>
        <a href="/homework" class="btn btn-primary">View All Homework</a>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2>Quick Actions</h2>
        <div class="button-group">
            <a href="/students/create" class="btn btn-primary">Add Student</a>
            <a href="/classes/create" class="btn btn-primary">Add Class</a>
            <a href="/subjects/create" class="btn btn-primary">Add Subject</a>
            <a href="/homework/create" class="btn btn-primary">Create Homework Request</a>
            <a href="/ai/quiz/generate" class="btn btn-secondary">Generate Quiz</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
