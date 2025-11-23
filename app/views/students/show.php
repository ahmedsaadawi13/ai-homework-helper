<!-- FILE: /app/views/students/show.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($student['name']); ?></h1>
        <a href="/students/<?php echo $student['id']; ?>/edit" class="btn btn-primary">Edit Student</a>
    </div>

    <!-- Student Info -->
    <div class="card">
        <h2>Student Information</h2>
        <dl class="info-list">
            <dt>Email:</dt>
            <dd><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></dd>

            <dt>Class:</dt>
            <dd><?php echo htmlspecialchars($student['class_name'] ?: 'N/A'); ?></dd>

            <dt>Grade Level:</dt>
            <dd><?php echo htmlspecialchars($student['grade_level'] ?: 'N/A'); ?></dd>

            <dt>Parent Name:</dt>
            <dd><?php echo htmlspecialchars($student['parent_name'] ?: 'N/A'); ?></dd>

            <dt>Parent Email:</dt>
            <dd><?php echo htmlspecialchars($student['parent_email'] ?: 'N/A'); ?></dd>

            <dt>Parent Phone:</dt>
            <dd><?php echo htmlspecialchars($student['parent_phone'] ?: 'N/A'); ?></dd>

            <dt>Status:</dt>
            <dd><span class="badge badge-<?php echo $student['status']; ?>"><?php echo $student['status']; ?></span></dd>
        </dl>
    </div>

    <!-- Performance Stats -->
    <div class="card">
        <h2>Performance Statistics</h2>
        <div class="card-grid">
            <div class="stat-card">
                <h3><?php echo $stats['homework_count']; ?></h3>
                <p>Homework Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['quizzes_taken']; ?></h3>
                <p>Quizzes Taken</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['average_score']; ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['ai_sessions']; ?></h3>
                <p>AI Sessions</p>
            </div>
        </div>
    </div>

    <!-- Recent Homework -->
    <div class="card">
        <h2>Recent Homework</h2>
        <?php if (!empty($recent_homework)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
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
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
