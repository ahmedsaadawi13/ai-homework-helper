<!-- FILE: /app/views/students/create.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Add New Student</h1>

    <div class="card">
        <form method="POST" action="/students/create">
            <?php echo View::csrfField(); ?>

            <div class="form-group">
                <label for="name">Student Name *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Student Email (optional)</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="">-- No Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="text" id="grade_level" name="grade_level" class="form-control" placeholder="e.g., Grade 10">
            </div>

            <h3>Parent Information</h3>

            <div class="form-group">
                <label for="parent_name">Parent Name</label>
                <input type="text" id="parent_name" name="parent_name" class="form-control">
            </div>

            <div class="form-group">
                <label for="parent_email">Parent Email</label>
                <input type="email" id="parent_email" name="parent_email" class="form-control">
            </div>

            <div class="form-group">
                <label for="parent_phone">Parent Phone</label>
                <input type="text" id="parent_phone" name="parent_phone" class="form-control">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Student</button>
                <a href="/students" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
