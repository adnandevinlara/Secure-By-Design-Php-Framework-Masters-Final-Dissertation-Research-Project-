<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12 text-center py-5 bg-light rounded shadow-sm border border-secondary border-opacity-10">
        <h1 class="fw-bold text-primary mb-3">Welcome to the Secure Blog</h1>
        <p class="text-muted lead">A demonstration of the Secure-by-Design PHP Framework.</p>
        
        <!-- Search Bar (Required by spec) -->
        <form action="/search" method="GET" class="d-flex justify-content-center mt-4 mx-auto" style="max-width: 500px;">
            <input type="text" name="q" class="form-control me-2" placeholder="Search posts..." required>
            <button type="submit" class="btn btn-primary px-4"><i class="bx bx-search"></i> Search</button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Main Blog Feed -->
    <div class="col-lg-8">
        <h4 class="mb-4 border-bottom pb-2">Latest Posts</h4>
        
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info-subtle text-info"><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></span>
                            <small class="text-muted"><i class="bx bx-calendar me-1"></i> <?= htmlspecialchars(date('F j, Y', strtotime($post['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        
                        <h4 class="card-title mb-3"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        
                        <p class="card-text text-muted">
                            <?= htmlspecialchars(substr($post['content'], 0, 150), ENT_QUOTES, 'UTF-8') ?>...
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <span class="text-muted font-size-13"><i class="bx bx-user-circle me-1"></i> <?= htmlspecialchars($post['author_name'] ?? 'Unknown Author', ENT_QUOTES, 'UTF-8') ?></span>
                            <!-- Link to the Post Details page -->
                            <a href="/post/view?id=<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary">Read More <i class="bx bx-right-arrow-alt"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info border-0 shadow-sm">
                No published posts found. Check back later!
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">About</h5>
                <p class="text-muted font-size-13 mb-0">This Mini CMS evaluates the effectiveness of framework-level security controls including Stored XSS mitigation, secure routing, and strict content encoding.</p>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>