<!-- FILE: /app/views/dashboard/platform_admin.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Platform Admin Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars(Auth::user()['name']); ?>!</p>

    <!-- Stats -->
    <div class="card-grid">
        <div class="stat-card">
            <h3><?php echo $total_tenants; ?></h3>
            <p>Total Tenants</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $active_tenants; ?></h3>
            <p>Active Tenants</p>
        </div>
    </div>

    <!-- Recent Tenants -->
    <div class="card">
        <h2>Recent Tenants</h2>
        <?php if (!empty($recent_tenants)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_tenants as $tenant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tenant['name']); ?></td>
                            <td><?php echo htmlspecialchars($tenant['type']); ?></td>
                            <td><?php echo htmlspecialchars($tenant['email']); ?></td>
                            <td><span class="badge badge-<?php echo $tenant['status']; ?>"><?php echo $tenant['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($tenant['created_at'])); ?></td>
                            <td><a href="/admin/tenants/<?php echo $tenant['id']; ?>/edit" class="btn btn-sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tenants yet.</p>
        <?php endif; ?>
        <a href="/admin/tenants" class="btn btn-primary">View All Tenants</a>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2>Quick Actions</h2>
        <div class="button-group">
            <a href="/admin/tenants/create" class="btn btn-primary">Add Tenant</a>
            <a href="/admin/tenants" class="btn btn-secondary">Manage Tenants</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
