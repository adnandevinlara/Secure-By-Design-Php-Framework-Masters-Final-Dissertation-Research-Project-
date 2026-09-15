<?php ob_start(); 
// Decode the JSON array from the database so we know which boxes to check
$activePerms = json_decode($subadmin['permissions'] ?? '[]', true) ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-edit me-1"></i> Edit Sub-Admin Permissions</h4>
            <div class="page-title-right">
                <a href="/subadmins" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>

<form action="/subadmin/update" method="POST">
    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
    <input type="hidden" name="user_id" value="<?= $subadmin['id'] ?>">
    
    <div class="row">
        <!-- Left Column: Editable Account Details -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Account Details</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($subadmin['username']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($subadmin['email']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Reset Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        <small class="text-muted">Only fill this if you want to change their password.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Pre-Checked Permissions -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pb-0">
                    <h5 class="fw-bold text-primary">Roles & Responsibilities</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        
                        <!-- Users -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">B. Manage Users</h6>
                            <?php foreach(['view_users'=>'View Users', 'edit_users'=>'Edit Users', 'delete_users'=>'Delete Users', 'status_users'=>'Update Status'] as $val => $label): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $val ?>" <?= in_array($val, $activePerms) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Posts -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">C. Manage Posts</h6>
                            <?php foreach(['view_blogs'=>'View Blogs', 'create_blog'=>'Create Blog', 'edit_blog'=>'Edit Blog', 'status_blog'=>'Change Status', 'delete_blog'=>'Delete Blog'] as $val => $label): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $val ?>" <?= in_array($val, $activePerms) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Comments -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">D. Manage Comments</h6>
                            <?php foreach(['view_comments'=>'View Comments', 'status_comment'=>'Change Status', 'reply_comment'=>'Reply to Comment', 'delete_comment'=>'Delete Comment'] as $val => $label): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $val ?>" <?= in_array($val, $activePerms) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Security -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">E. Manage Securities</h6>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_security" <?= in_array('view_security', $activePerms) ? 'checked' : '' ?>>
                                <label class="form-check-label">View Security Alerts</label>
                            </div>
                        </div>

                    </div>
                    
                    <div class="text-end mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5"><i class="bx bx-save me-1"></i> Save Permissions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>