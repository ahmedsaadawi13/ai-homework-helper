<!-- FILE: /app/views/dashboard/student.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Student Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($student['name']); ?>!</p>

    <!-- Performance Stats -->
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

    <!-- My Homework -->
    <div class="card">
        <h2>My Homework</h2>
        <?php if (!empty($my_homework)): ?>
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
                    <?php foreach ($my_homework as $hw): ?>
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
        <a href="/homework/create" class="btn btn-primary">Ask for Help</a>
    </div>

    <!-- Recent Quiz Results -->
    <?php if (!empty($quiz_results)): ?>
        <div class="card">
            <h2>Recent Quiz Results</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Subject</th>
                        <th>Score</th>
                        <th>Passed</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($quiz_results, 0, 5) as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                            <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                            <td><?php echo $result['score']; ?>%</td>
                            <td><span class="badge badge-<?php echo $result['passed'] ? 'success' : 'danger'; ?>">
                                <?php echo $result['passed'] ? 'Yes' : 'No'; ?>
                            </span></td>
                            <td><?php echo date('M d, Y', strtotime($result['completed_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
