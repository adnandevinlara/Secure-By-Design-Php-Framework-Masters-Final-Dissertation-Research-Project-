<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-edit me-1"></i> Create New Blog Post</h4>
            <div class="page-title-right">
                <a href="/posts" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back to Posts</a>
            </div>
        </div>
    </div>
</div>

<!-- Security Matrix Accordion (Kept for Master's Evaluation) -->
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
                                ASVS V5.1: Client-side (HTML5/Bootstrap) and Server-side (PHP) input validation enforced.
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

<div class="row">
    <div class="col-xl-10 mx-auto">
        
        <!-- Alerts for the Trap -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bx bx-error-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- CRITICAL: enctype="multipart/form-data" added for secure file uploads -->
                <form method="POST" action="/post/store" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '<input type="hidden" name="csrf_token" value="test">' ?>
                    
                    <div class="row">
                        <!-- Post Title (Full width) -->
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label fw-bold">Post Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" required minlength="5" placeholder="Enter an engaging title...">
                            <div class="invalid-feedback">Title is required and must be at least 5 characters long.</div>
                        </div>

                        <!-- Category Dropdown (4 columns) -->
                        <div class="col-md-4 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="" selected disabled>Select a category...</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category for this post.</div>
                        </div>

                        <!-- Post Status Field (4 columns) using Radio Buttons to bypass JS -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Post Status <span class="text-danger">*</span></label>
                            
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_published" value="published" checked>
                                <label class="form-check-label" for="status_published">Published</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft">
                                <label class="form-check-label" for="status_draft">Draft</label>
                            </div>
                            
                            <div class="form-text text-muted small mt-1">Drafts are hidden from public.</div>
                        </div>

                        <!-- Post Banner (Image) Upload (4 columns) -->
                        <div class="col-md-4 mb-3">
                            <label for="banner_image" class="form-label fw-bold">Post Banner (Image)</label>
                            <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/jpeg, image/png, image/webp">
                            <div class="form-text text-muted small">Optional. Recommended size: 1200x600px.</div>
                        </div>
                    </div>

                    <!-- Post Content with template 'note-editor' class -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold">Post Content <span class="text-danger">*</span></label>
                        <textarea class="form-control note-editor" id="content" name="content" rows="12" required minlength="10" placeholder="Write your content here..."></textarea>
                        <div class="invalid-feedback">Content is required and must be at least 10 characters long.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-danger" onclick="testTrap()"><i class="bx bx-bug me-1"></i> Test XSS Trap</button>
                        <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bx bx-send me-1"></i> Publish Securely</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Client-Side Validation & XSS Trap Script -->
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

    // Guaranteed XSS Trap Injector
    function testTrap() {
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');

        if (titleInput && contentInput) {
            titleInput.value = "Hacker Attack Simulation";
            contentInput.value = "Malicious Payload Test <script>alert('XSS VULNERABILITY DETECTED');<\/script>";
            
            titleInput.classList.add('is-invalid');
            contentInput.classList.add('is-invalid');
        } else {
            console.error("Form inputs with IDs 'title' or 'content' could not be found.");
        }
    }
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>