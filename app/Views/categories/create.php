<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-folder-plus me-1"></i> Create New Category</h4>
            <div class="page-title-right">
                <a href="/categories" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <form action="/category/store" method="POST">
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="mb-4">
                        <!-- <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg" required placeholder="e.g., Cyber Security">
                        <div class="form-text">This will appear in the blog sidebar.</div> -->

                        <label>Category Name</label>
                        <input type="text" name="name" id="category_name" class="form-control" required autocomplete="off">
                        <span id="name_error" class="text-danger" style="display:none; font-size: 0.9em; margin-top: 5px;">⚠️ This category already exists!</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Visibility Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-lg" required>
                            <option value="show">Show (Active & Visible)</option>
                            <option value="hide">Hide (Draft Mode)</option>
                        </select>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bx bx-save me-1"></i> Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const nameInput = document.getElementById('category_name');
    const errorSpan = document.getElementById('name_error');
    
    // Replace 'submit_btn' with the actual ID of your form's submit button
    const submitBtn = document.querySelector('button[type="submit"]'); 

    nameInput.addEventListener('keyup', function() {
        let name = this.value.trim();
        
        if(name.length > 0) {
            fetch('/category/check-name', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'name=' + encodeURIComponent(name)
            })
            .then(response => response.json())
            .then(data => {
                if(data.exists) {
                    errorSpan.style.display = 'block';
                    submitBtn.disabled = true; // Lock the button
                } else {
                    errorSpan.style.display = 'none';
                    submitBtn.disabled = false; // Unlock the button
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            errorSpan.style.display = 'none';
            submitBtn.disabled = false;
        }
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main.php'; 
?>