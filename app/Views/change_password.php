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

<!-- Success Modal -->
<?php if (isset($_SESSION['password_updated_modal'])): ?>
<div class="modal fade" id="passwordSuccessModal" tabindex="-1" aria-labelledby="passwordSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white border-0">
        <h5 class="modal-title" id="passwordSuccessModalLabel"><i class="bx bx-check-circle me-2"></i>Success</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <h5 class="text-success mb-3"><?= $_SESSION['password_updated_modal']; ?></h5>
        <p class="text-muted">You can now use your new password for future logins.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var successModal = new bootstrap.Modal(document.getElementById('passwordSuccessModal'));
    successModal.show();
});
</script>
<?php unset($_SESSION['password_updated_modal']); endif; ?>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>