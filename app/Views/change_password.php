<?php ob_start(); ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="bx bx-lock-alt me-2"></i>Update Password</h4>
                    <p class="text-muted">Securely change your account password.</p>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/change-password/process" method="POST">
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Update Password</button>
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