<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-purchase-tag-alt me-1"></i> Categories</h4>
        </div>
    </div>
</div>

<!-- Security Matrix Accordion -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="securityAccordion">
            <div class="accordion-item border-primary border-2">
                <h2 class="accordion-header" id="headingSecurity">
                    <button class="accordion-button collapsed bg-primary-subtle text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="bx bx-check-shield me-2 fs-5"></i> Active Security Controls & ASVS Matrix (Expand to View)
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-primary"><i class="bx bx-check-circle me-1"></i> Dual-Layer Validation:</span> 
                                ASVS V5.1: Client-side (HTML5/Bootstrap) and Server-side (PHP) input validation enforced on the submission form.
                            </li>
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-primary"><i class="bx bx-check-circle me-1"></i> Malicious Code Trap:</span> 
                                ASVS V5.3: Active scanning intercepts unencoded XSS and SQLi payloads, neutralizing them and logging the attacker's IP.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bx bx-error-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bx bx-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="row">
    <!-- Add New Category Form -->
    <div class="col-xl-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4">Add New Category</h5>
                <form method="POST" action="/categories/store" class="needs-validation" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '<input type="hidden" name="csrf_token" value="test">' ?>
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" id="cat_name" name="name" required minlength="3" placeholder="e.g., Technology">
                        <div class="invalid-feedback">
                            Category name is required (Min: 3 characters).
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add Category</button>
                        <button type="button" class="btn btn-outline-danger mt-2" onclick="testCatTrap()"><i class="bx bx-bug me-1"></i> Test XSS Trap</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories Data Table -->
    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= htmlspecialchars($cat['id']) ?></td>
                                        <td class="fw-bold text-dark"><span class="badge bg-info-subtle text-info font-size-13"><?= htmlspecialchars($cat['name']) ?></span></td>
                                        <td class="text-muted"><small><?= htmlspecialchars($cat['created_at']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Bootstrap Validation
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

    // XSS Trap Script
    function testCatTrap() {
        const nameInput = document.getElementById('cat_name');
        if (nameInput) {
            nameInput.value = "<script>alert('Category Hack!');<\/script>";
            nameInput.classList.add('is-invalid');
        }
    }
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>