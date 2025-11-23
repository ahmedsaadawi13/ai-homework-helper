<!-- FILE: /app/views/homework/show.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($homework['title']); ?></h1>
        <span class="badge badge-<?php echo $homework['status']; ?>"><?php echo $homework['status']; ?></span>
    </div>

    <!-- Homework Details -->
    <div class="card">
        <h2>Question</h2>
        <dl class="info-list">
            <dt>Student:</dt>
            <dd><?php echo htmlspecialchars($homework['student_name']); ?></dd>

            <dt>Subject:</dt>
            <dd><?php echo htmlspecialchars($homework['subject_name']); ?></dd>

            <dt>Class:</dt>
            <dd><?php echo htmlspecialchars($homework['class_name'] ?: 'N/A'); ?></dd>

            <dt>Created:</dt>
            <dd><?php echo date('M d, Y H:i', strtotime($homework['created_at'])); ?></dd>
        </dl>

        <h3>Description</h3>
        <p><?php echo nl2br(htmlspecialchars($homework['description'])); ?></p>

        <?php if ($homework['image']): ?>
            <h3>Uploaded Image</h3>
            <img src="/storage/uploads/<?php echo htmlspecialchars($homework['image']); ?>" alt="Homework Image" class="homework-image">
        <?php endif; ?>

        <!-- Request AI Help Button -->
        <?php if ($homework['status'] === 'new' || $homework['status'] === 'in_progress'): ?>
            <form method="POST" action="/homework/<?php echo $homework['id']; ?>/ask-ai">
                <?php echo View::csrfField(); ?>
                <button type="submit" class="btn btn-primary">Get AI Help</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- AI Responses -->
    <?php if (!empty($responses)): ?>
        <div class="card">
            <h2>Responses</h2>
            <?php foreach ($responses as $response): ?>
                <div class="response <?php echo $response['response_type']; ?>">
                    <h4>
                        <?php if ($response['response_type'] === 'ai'): ?>
                            AI Answer
                        <?php elseif ($response['response_type'] === 'teacher'): ?>
                            Teacher Comment - <?php echo htmlspecialchars($response['user_name'] ?? 'Teacher'); ?>
                        <?php endif; ?>
                    </h4>
                    <p><strong>Answer:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($response['content'])); ?></p>

                    <?php if ($response['step_by_step']): ?>
                        <p><strong>Step-by-Step:</strong></p>
                        <pre><?php echo htmlspecialchars($response['step_by_step']); ?></pre>
                    <?php endif; ?>

                    <?php if ($response['examples']): ?>
                        <p><strong>Examples:</strong></p>
                        <pre><?php echo htmlspecialchars($response['examples']); ?></pre>
                    <?php endif; ?>

                    <small>Posted: <?php echo date('M d, Y H:i', strtotime($response['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Status Update (for teachers/admins) -->
    <?php if (Auth::hasRole(['tenant_admin', 'teacher'])): ?>
        <div class="card">
            <h3>Update Status</h3>
            <form method="POST" action="/homework/<?php echo $homework['id']; ?>/status">
                <?php echo View::csrfField(); ?>
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="new" <?php echo $homework['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo $homework['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="answered" <?php echo $homework['status'] === 'answered' ? 'selected' : ''; ?>>Answered</option>
                        <option value="reviewed" <?php echo $homework['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="closed" <?php echo $homework['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Update Status</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
