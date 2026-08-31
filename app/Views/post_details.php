<?php ob_start(); ?>

<div class="row">
    <!-- Main Article Content -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-5">
            
            <!-- NEW: Dynamic Hero Banner Image Display -->
            <?php if (!empty($post['banner_image'])): ?>
                <img src="<?= htmlspecialchars($post['banner_image'], ENT_QUOTES, 'UTF-8') ?>" 
                     class="img-fluid rounded-top w-100" 
                     alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>" 
                     style="max-height: 400px; object-fit: cover;">
            <?php else: ?>
                <!-- Fallback Placeholder -->
                <div class="bg-light text-center d-flex align-items-center justify-content-center rounded-top" style="height: 250px;">
                    <i class="bx bx-image text-muted" style="font-size: 5rem;"></i>
                </div>
            <?php endif; ?>
            
            <div class="card-body p-4 p-md-5">
                <div class="text-muted mb-3 font-size-14">
                    <i class="bx bx-purchase-tag-alt text-primary me-1"></i> <span class="fw-medium"><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="mx-2">|</span>
                    <i class="bx bx-user text-primary me-1"></i> <span class="fw-medium"><?= htmlspecialchars($post['author_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="mx-2">|</span>
                    <i class="bx bx-time-five text-primary me-1"></i> <span class="fw-medium"><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                </div>
                
                <h1 class="fw-bold mb-4"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                
                <!-- Post Content -->
                <div class="post-content fs-5 text-secondary" style="line-height: 1.8;">
                    <?= $post['content'] // Raw output allowed ONLY if sanitized through a safe Note Editor previously! ?>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-4 p-md-5">
                <h4 class="fw-bold mb-4"><i class="bx bx-comment-detail text-primary me-2"></i> Comments (<?= count($comments) ?>)</h4>
                
                <!-- Display Approved Comments -->
                <?php if (empty($comments)): ?>
                    <p class="text-muted">No comments yet. Be the first to share your thoughts!</p>
                <?php else: ?>
                    <div class="mb-5">
                        <?php foreach ($comments as $comment): ?>
                            <div class="d-flex mb-4 bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">
                                        <?= strtoupper(substr(htmlspecialchars($comment['author'] ?? 'U'), 0, 1)) ?>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($comment['author'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></h6>
                                    <p class="text-muted small mb-2"><?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?></p>
                                    <p class="mb-0 text-secondary"><?= nl2br(htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Submit a Comment Form (Adnan's Point #10: Restrict to Registered Users) -->
                <h5 class="fw-bold mb-3">Leave a Reply</h5>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Form for Authenticated Users -->
                    <form action="/comment/store" method="POST">
                        <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Comment</label>
                            <textarea name="content" class="form-control" rows="4" required placeholder="Share your thoughts..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-send me-1"></i> Post Comment</button>
                    </form>
                <?php else: ?>
                    <!-- Prompt for Guest Users -->
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                        <i class="bx bx-info-circle fs-4 me-3 text-warning"></i>
                        <div>
                            <strong>Want to join the conversation?</strong> You must be logged in to leave a comment. 
                            <a href="/login" class="text-primary fw-bold text-decoration-none ms-1">Login</a> or 
                            <a href="/register" class="text-primary fw-bold text-decoration-none">Register</a>.
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Sidebar Content (Categories) -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Categories</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item border-0 py-3">
                        <a href="/" class="text-body text-decoration-none"><i class="bx bx-chevron-right text-primary me-2"></i> All Categories</a>
                    </li>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <li class="list-group-item border-0 py-3">
                                <a href="/?category_id=<?= $cat['id'] ?>" class="text-body text-decoration-none">
                                    <i class="bx bx-chevron-right text-primary me-2"></i> <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php 
// FIX: Using the public layout!
$content = ob_get_clean(); 
require 'layouts/public.php'; 
?>