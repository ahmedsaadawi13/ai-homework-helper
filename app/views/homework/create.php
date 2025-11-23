<!-- FILE: /app/views/homework/create.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Create Homework Help Request</h1>

    <div class="card">
        <form method="POST" action="/homework/create" enctype="multipart/form-data">
            <?php echo View::csrfField(); ?>

            <div class="form-group">
                <label for="student_id">Student *</label>
                <select id="student_id" name="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject_id">Subject *</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">Question Title *</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g., Help with quadratic equations">
            </div>

            <div class="form-group">
                <label for="description">Description/Question *</label>
                <textarea id="description" name="description" class="form-control" rows="6" required placeholder="Describe your homework question in detail..."></textarea>
            </div>

            <div class="form-group">
                <label for="image">Upload Image (optional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small class="form-text">You can upload a photo of your homework question</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="/homework" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
