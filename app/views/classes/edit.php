<!-- FILE: /app/views/classes/edit.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
    <h1>Edit Class</h1>
    <div class="card">
        <form method="POST" action="/classes/<?php echo $class['id']; ?>/edit">
            <?php echo View::csrfField(); ?>
            <div class="form-group">
                <label for="name">Class Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($class['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="text" id="grade_level" name="grade_level" class="form-control" value="<?php echo htmlspecialchars($class['grade_level']); ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($class['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $class['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="archived" <?php echo $class['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Class</button>
                <a href="/classes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
