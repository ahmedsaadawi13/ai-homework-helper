<!-- FILE: /app/views/homework/index.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Homework Help Requests</h1>
        <a href="/homework/create" class="btn btn-primary">New Request</a>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" action="/homework">
            <div class="form-row">
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo $filters['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="answered" <?php echo $filters['status'] === 'answered' ? 'selected' : ''; ?>>Answered</option>
                        <option value="reviewed" <?php echo $filters['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="subject_id" class="form-control">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo $filters['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Homework List -->
    <div class="card">
        <?php if (!empty($homework)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($homework as $hw): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hw['title']); ?></td>
                            <td><?php echo htmlspecialchars($hw['student_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($hw['subject_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-<?php echo $hw['status']; ?>"><?php echo $hw['status']; ?></span></td>
                            <td><span class="badge badge-<?php echo $hw['priority']; ?>"><?php echo $hw['priority']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($hw['created_at'])); ?></td>
                            <td><a href="/homework/<?php echo $hw['id']; ?>" class="btn btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php echo $paginator->render('/homework'); ?>
        <?php else: ?>
            <p>No homework requests found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
