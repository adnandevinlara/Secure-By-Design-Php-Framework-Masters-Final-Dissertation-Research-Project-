<?php ob_start(); ?>

<div class="row justify-content-center mt-5" style="min-height: 60vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="bx bx-shield-quarter me-2"></i>Secure Login</h4>
                    <div class="badge bg-warning text-dark mb-2 px-3 py-2">
                        🛡️ Protected by CSRF Middleware & Anti-Session Fixation
                    </div>
                    <p class="text-muted">Sign in to access the Secure CMS.</p>
                </div>
                
                <?php if (isset($_SESSION['test_auth_status'])): ?>
                    <div class="alert alert-danger shadow-sm border-0 text-center">
                        <!-- SECURE: Context-aware escaping prevents Reflected XSS -->
                        <?= htmlspecialchars($_SESSION['test_auth_status'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php unset($_SESSION['test_auth_status']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="/login" method="POST">
                    <!-- CRITICAL: Injecting the CSRF Token -->
                    <?= \Core\Security\Csrf::getFormField(); ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control form-control-lg" required placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0">Password</label>
                            <a href="/forgot-password" class="text-decoration-none text-primary small fw-medium">Forgot password?</a>
                        </div>
                        <input type="password" name="password" class="form-control form-control-lg" required placeholder="Enter your password">
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">Log In</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <span class="text-muted">Need an account?</span> 
                    <a href="/register" class="text-primary fw-bold text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// FIX: Using the public layout instead of the dashboard main layout!
$content = ob_get_clean(); 
require 'layouts/public.php'; 
?>