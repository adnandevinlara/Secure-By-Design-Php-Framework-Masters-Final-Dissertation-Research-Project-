<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-shield-quarter me-1"></i> Create Sub-Admin</h4>
            <div class="page-title-right">
                <a href="/subadmins" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form action="/subadmin/store" method="POST">
    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
    
    <div class="row">
        <!-- Left Column: Account Details -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pb-0">
                    <h5 class="fw-bold text-primary">Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Temporary Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    
                    <div class="mb-3 mt-4 p-3 bg-light rounded border">
                        <label class="form-label fw-bold mb-1">A. User Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked onclick="return false;">
                            <label class="form-check-label fw-bold text-primary">Sub-Admin</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pb-0">
                    <h5 class="fw-bold text-primary">Roles & Responsibilities</h5>
                    <p class="text-muted small">Select the specific modules this Sub-Admin can access.</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        
                        <!-- Requirement B: Manage Users -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">B. Manage Users</h6>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_users" id="p_vu">
                                <label class="form-check-label" for="p_vu">View Users</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="edit_users" id="p_eu">
                                <label class="form-check-label" for="p_eu">Edit Users</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="delete_users" id="p_du">
                                <label class="form-check-label" for="p_du">Delete Users</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="status_users" id="p_su">
                                <label class="form-check-label" for="p_su">Update User Status</label>
                            </div>
                        </div>

                        <!-- Requirement C: Manage Posts -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">C. Manage Posts</h6>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_blogs" id="p_vb">
                                <label class="form-check-label" for="p_vb">View Blogs</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="create_blog" id="p_cb">
                                <label class="form-check-label" for="p_cb">Create Blog</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="edit_blog" id="p_eb">
                                <label class="form-check-label" for="p_eb">Edit Blog</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="status_blog" id="p_sb">
                                <label class="form-check-label" for="p_sb">Change Status</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="delete_blog" id="p_db">
                                <label class="form-check-label" for="p_db">Delete Blog</label>
                            </div>
                        </div>

                        <!-- Requirement D: Manage Comments -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">D. Manage Comments</h6>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_comments" id="p_vc">
                                <label class="form-check-label" for="p_vc">View Comments</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="status_comment" id="p_sc">
                                <label class="form-check-label" for="p_sc">Change Status (Hide/Show)</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="reply_comment" id="p_rc">
                                <label class="form-check-label" for="p_rc">Reply to Comment</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="delete_comment" id="p_dc">
                                <label class="form-check-label" for="p_dc">Delete Comment</label>
                            </div>
                        </div>

                        <!-- Requirement E: Manage Securities -->
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold border-bottom pb-2">E. Manage Securities</h6>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_security" id="p_vs">
                                <label class="form-check-label" for="p_vs">View Security Alerts & Events</label>
                            </div>
                        </div>

                    </div>
                    
                    <div class="text-end mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5"><i class="bx bx-check-circle me-1"></i> Create Sub-Admin Account</button>
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