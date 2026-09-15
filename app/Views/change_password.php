<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-header bg-white border-bottom pb-0 pt-4 text-center">
                <h4 class="text-primary fw-bold"><i class="bx bx-lock-alt me-1"></i> Change Password</h4>
                <p class="text-muted small">Securely update your account password.</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0 py-2"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/change-password" method="POST">
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                        <small class="text-muted">Must be at least 8 characters.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bx bx-save me-1"></i> Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>