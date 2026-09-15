<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-user-check me-1"></i> Manage Users</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                
                <!-- NEW: Phase 2 Search & Filter Form -->
                <form method="GET" action="/users" class="row g-3 mb-4 align-items-end bg-light p-3 rounded">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Account Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Deactivated</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">User Type</label>
                        <select name="role" class="form-select">
                            <option value="">All User Types</option>
                            <option value="super_admin" <?= ($_GET['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            <option value="sub_admin" <?= ($_GET['role'] ?? '') === 'sub_admin' ? 'selected' : '' ?>>Sub Admin</option>
                            <option value="user" <?= ($_GET['role'] ?? '') === 'user' ? 'selected' : '' ?>>Registered User</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                        <a href="/users" class="btn btn-outline-secondary ms-2">Reset</a>
                    </div>
                </form>
                <!-- End Filter Form -->

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></td>
                                        <td>
                                            <span class="badge bg-info font-size-12"><?= ucfirst(htmlspecialchars($user['role'], ENT_QUOTES)) ?></span>
                                        </td>
                                        
                                        <!-- NEW: Display Active/Deactivated Status -->
                                        <td>
                                            <?php if (isset($user['is_active']) && $user['is_active'] == 0): ?>
                                                <span class="badge bg-danger">Deactivated</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        
                                        <td>
                                            <!-- Activate/Deactivate Toggle Button -->
                                            <form method="POST" action="/users/toggle-status" class="d-inline">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="is_active" value="<?= (isset($user['is_active']) && $user['is_active'] == 0) ? 1 : 0 ?>">
                                                
                                                <?php if (isset($user['is_active']) && $user['is_active'] == 0): ?>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Activate User" onclick="return confirm('Activate this user account?');">
                                                        <i class="bx bx-check-circle"></i> Activate
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Deactivate User" onclick="return confirm('Deactivate this user? They will not be able to log in.');">
                                                        <i class="bx bx-block"></i> Deactivate
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Existing Delete Button -->
                                            <form method="POST" action="/users/delete" class="d-inline">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger ms-1" title="Permanently Delete User" onclick="return confirm('WARNING: This will permanently delete the user and ALL their posts, comments, and images. Are you sure?');">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No users found matching your filters.</td>
                                </tr>
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