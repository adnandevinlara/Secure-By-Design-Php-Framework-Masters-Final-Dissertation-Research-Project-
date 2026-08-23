<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Back Button -->
        <a href="/" class="btn btn-sm btn-light shadow-sm mb-4">
            <i class="bx bx-left-arrow-alt"></i> Back to Home
        </a>

        <!-- Post Content -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-5">
                <div class="mb-4 text-center">
                    <span class="badge bg-info-subtle text-info mb-2"><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></span>
                    <h1 class="fw-bold mb-3"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="text-muted">
                        <span><i class="bx bx-user-circle me-1"></i> <?= htmlspecialchars($post['author_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="mx-2">|</span>
                        <span><i class="bx bx-calendar me-1"></i> <?= htmlspecialchars(date('F j, Y', strtotime($post['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                
                <hr class="mb-4">
                
                <!-- SECURE: Output encoded to prevent XSS -->
                <div class="post-content" style="white-space: pre-wrap;">
                    <?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>

        <!-- NEW: Display Approved Comments -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3"><i class="bx bx-chat me-2"></i> Comments (<?= count($comments ?? []) ?>)</h5>
            
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="card shadow-sm mb-3 border-0 bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between mb-2">
                                <!-- Securely escaping the author and date -->
                                <strong class="text-primary"><?= htmlspecialchars($comment['author'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></strong>
                                <small class="text-muted"><?= htmlspecialchars(date('M j, Y, g:i A', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                            <!-- Securely escaping the comment content to prevent Stored XSS -->
                            <p class="mb-0 text-dark" style="white-space: pre-wrap;"><?= htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-secondary border-0 text-center text-muted shadow-sm">
                    No comments yet. Be the first to share your thoughts!
                </div>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <h4 class="mb-4"><i class="bx bx-comment-detail me-2"></i>Leave a Comment</h4>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Secure Comment Form -->
                    <form action="/comments/store" method="POST">
                        <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                        
                        <!-- CRITICAL: This hidden field sends the Post ID to the controller -->
                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="mb-3">
                            <textarea name="content" rows="4" class="form-control" placeholder="Write your comment here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i> Publish Comment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center border-0 shadow-sm">
                        Please <a href="/login" class="fw-bold">Login</a> to leave a comment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>