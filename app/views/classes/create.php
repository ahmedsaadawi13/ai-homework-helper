<!-- FILE: /app/views/classes/create.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
    <h1>Create Class</h1>
    <div class="card">
        <form method="POST" action="/classes/create">
            <?php echo View::csrfField(); ?>
            <div class="form-group">
                <label for="name">Class Name *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="text" id="grade_level" name="grade_level" class="form-control">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Class</button>
                <a href="/classes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
