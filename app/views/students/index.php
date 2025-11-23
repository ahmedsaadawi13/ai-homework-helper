<!-- FILE: /app/views/students/index.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Students</h1>
        <a href="/students/create" class="btn btn-primary">Add Student</a>
    </div>

    <!-- Search -->
    <div class="card">
        <form method="GET" action="/students">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-secondary">Search</button>
            </div>
        </form>
    </div>

    <!-- Students List -->
    <div class="card">
        <?php if (!empty($students)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Grade Level</th>
                        <th>Parent Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['class_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['grade_level'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_email'] ?? '-'); ?></td>
                            <td><span class="badge badge-<?php echo $student['status']; ?>"><?php echo $student['status']; ?></span></td>
                            <td>
                                <a href="/students/<?php echo $student['id']; ?>" class="btn btn-sm">View</a>
                                <a href="/students/<?php echo $student['id']; ?>/edit" class="btn btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php echo $paginator->render('/students'); ?>
        <?php else: ?>
            <p>No students found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
