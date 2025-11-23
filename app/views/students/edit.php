<!-- FILE: /app/views/students/edit.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Edit Student</h1>

    <div class="card">
        <form method="POST" action="/students/<?php echo $student['id']; ?>/edit">
            <?php echo View::csrfField(); ?>

            <div class="form-group">
                <label for="name">Student Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Student Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>">
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="">-- No Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $student['class_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="text" id="grade_level" name="grade_level" class="form-control" value="<?php echo htmlspecialchars($student['grade_level']); ?>">
            </div>

            <div class="form-group">
                <label for="parent_name">Parent Name</label>
                <input type="text" id="parent_name" name="parent_name" class="form-control" value="<?php echo htmlspecialchars($student['parent_name']); ?>">
            </div>

            <div class="form-group">
                <label for="parent_email">Parent Email</label>
                <input type="email" id="parent_email" name="parent_email" class="form-control" value="<?php echo htmlspecialchars($student['parent_email']); ?>">
            </div>

            <div class="form-group">
                <label for="parent_phone">Parent Phone</label>
                <input type="text" id="parent_phone" name="parent_phone" class="form-control" value="<?php echo htmlspecialchars($student['parent_phone']); ?>">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $student['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $student['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Student</button>
                <a href="/students/<?php echo $student['id']; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <?php if (Auth::hasRole('tenant_admin')): ?>
            <hr>
            <form method="POST" action="/students/<?php echo $student['id']; ?>/delete" onsubmit="return confirm('Are you sure you want to delete this student?');">
                <?php echo View::csrfField(); ?>
                <button type="submit" class="btn btn-danger">Delete Student</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
