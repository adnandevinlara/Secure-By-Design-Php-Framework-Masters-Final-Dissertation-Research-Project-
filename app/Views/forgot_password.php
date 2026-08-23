<?php ob_start(); ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="bx bx-envelope me-2"></i>Forgot Password</h4>
                    <p class="text-muted">Enter your registered email address to receive an OTP and reset link.</p>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success shadow-sm border-0"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/forgot-password/send" method="POST">
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" required placeholder="name@example.com">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Confirm</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <a href="/login" class="text-muted text-decoration-none"><i class="bx bx-arrow-back me-1"></i>Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>