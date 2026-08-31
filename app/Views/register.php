<?php ob_start(); ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="bx bx-user-plus me-2"></i>Create an Account</h4>
                    <p class="text-muted">Join the Secure CMS platform.</p>
                </div>
                
                <!-- Display Error Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['test_auth_status'])): ?>
                    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['test_auth_status']; unset($_SESSION['test_auth_status']); ?></div>
                <?php endif; ?>

                <form action="/register" method="POST" class="needs-validation" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control form-control-lg" required minlength="3" placeholder="Choose a username">
                        <div class="invalid-feedback">Username is required (min 3 characters).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-lg" required placeholder="name@example.com">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Secure Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-lg" required placeholder="Minimum 8 chars, 1 uppercase, 1 number">
                        <div class="invalid-feedback">Password is required.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirm" class="form-control form-control-lg" required placeholder="Confirm your password">
                        <div class="invalid-feedback">Please confirm your password.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Create Account Securely</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <span class="text-muted">Already have an account?</span> 
                    <a href="/login" class="text-primary fw-bold text-decoration-none">Log In here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })();
</script>

<?php 
// FIX: Using the public layout instead of the dashboard main layout!
$content = ob_get_clean(); 
require 'layouts/public.php'; 
?>