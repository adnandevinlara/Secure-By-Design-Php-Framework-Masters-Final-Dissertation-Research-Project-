<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-shield-quarter me-1"></i> Sub-Admins List</h4>
            <div class="page-title-right">
                <a href="/subadmin/create" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Create Sub-Admin</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success shadow-sm border-0"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Permissions Granted</th>
                                <th>Created On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subadmins)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No Sub-Admins created yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subadmins as $admin): ?>
                                    <?php 
                                        // Decode the JSON back into an array to count the permissions
                                        $perms = json_decode($admin['permissions'], true) ?? [];
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($admin['username'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($admin['email'], ENT_QUOTES) ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= count($perms) ?> permissions active</span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>Edit (Demo)</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>