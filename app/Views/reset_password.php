<?php ob_start(); ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="bx bx-lock-open-alt me-2"></i>Create New Password</h4>
                    <p class="text-muted">Please enter the 6-digit OTP sent to your email and your new password.</p>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/reset-password/process" method="POST">
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <!-- We can grab the email from the URL parameter to save the user from typing it again -->
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">6-Digit OTP</label>
                        <input type="text" name="otp" class="form-control form-control-lg" required maxlength="6" placeholder="123456" autocomplete="off">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required minlength="8" placeholder="Enter new password">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="password_confirm" class="form-control form-control-lg" required minlength="8" placeholder="Confirm new password">
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