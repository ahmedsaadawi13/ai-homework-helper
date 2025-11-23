<!-- FILE: /app/views/classes/index.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>Classes</h1>
        <a href="/classes/create" class="btn btn-primary">Add Class</a>
    </div>
    <div class="card">
        <?php if (!empty($classes)): ?>
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Grade Level</th><th>Students</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                            <td><?php echo htmlspecialchars($class['grade_level'] ?: '-'); ?></td>
                            <td><?php echo $class['student_count']; ?></td>
                            <td><span class="badge badge-<?php echo $class['status']; ?>"><?php echo $class['status']; ?></span></td>
                            <td><a href="/classes/<?php echo $class['id']; ?>/edit" class="btn btn-sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No classes found.</p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
