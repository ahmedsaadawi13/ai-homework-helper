<!-- FILE: /app/views/dashboard/teacher.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Teacher Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars(Auth::user()['name']); ?>!</p>

    <!-- Stats -->
    <div class="card-grid">
        <div class="stat-card">
            <h3><?php echo $total_students; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?php echo count($pending_homework); ?></h3>
            <p>Pending Homework</p>
        </div>
        <div class="stat-card">
            <h3><?php echo count($recent_homework); ?></h3>
            <p>Recent Homework</p>
        </div>
    </div>

    <!-- Pending Homework -->
    <div class="card">
        <h2>Pending Homework Requests</h2>
        <?php if (!empty($pending_homework)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_homework as $hw): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hw['title']); ?></td>
                            <td><?php echo htmlspecialchars($hw['student_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($hw['subject_name'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($hw['created_at'])); ?></td>
                            <td><a href="/homework/<?php echo $hw['id']; ?>" class="btn btn-sm">Review</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending homework requests.</p>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2>Quick Actions</h2>
        <div class="button-group">
            <a href="/homework" class="btn btn-primary">View All Homework</a>
            <a href="/ai/quiz/generate" class="btn btn-secondary">Generate Quiz</a>
            <a href="/students" class="btn btn-secondary">View Students</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
