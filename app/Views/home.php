<?php ob_start(); ?>

<div class="row">
    <!-- Main Blog Content (Left Side) -->
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold text-uppercase"><?= isset($searchQuery) ? 'Search Results' : 'Latest Posts' ?></h4>
        </div>
        
        <?php if (empty($posts)): ?>
            <div class="alert alert-info shadow-sm border-0">No published posts found at the moment. Check back later!</div>
        <?php else: ?>
            <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        
                        <!-- NEW: Dynamic Banner Image Display (With Content Fallback) -->
                        <?php 
                        $displayImage = $post['banner_image'] ?? null;
                        
                        // If no banner was uploaded, extract the first image from the text editor content
                        if (empty($displayImage)) {
                            preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post['content'] ?? '', $matches);
                            $displayImage = $matches[1] ?? null;
                        }
                        ?>
                        
                        <?php if (!empty($displayImage)): ?>
                            <img src="<?= htmlspecialchars($displayImage, ENT_QUOTES, 'UTF-8') ?>" 
                                 class="card-img-top border-bottom" 
                                 alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                 style="height: 180px; object-fit: cover; width: 100%;">
                        <?php else: ?>
                            <!-- Fallback Placeholder -->
                            <div class="bg-light text-center d-flex align-items-center justify-content-center rounded-top border-bottom" style="height: 180px;">
                                <i class="bx bx-image text-muted" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="text-muted mb-2 font-size-12">
                                <i class="bx bx-purchase-tag-alt text-primary me-1"></i> <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?>
                                <span class="mx-2">|</span>
                                <i class="bx bx-time-five text-primary me-1"></i> <?= date('d M, Y', strtotime($post['created_at'])) ?>
                            </div>
                            
                            <h5 class="card-title fw-bold mb-3"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                            
                            <p class="card-text text-muted mb-4">
                                <?= htmlspecialchars(substr(strip_tags($post['content'] ?? ''), 0, 90), ENT_QUOTES, 'UTF-8') ?>...
                            </p>
                            
                            <div class="mt-auto">
                                <a href="/post/view?id=<?= $post['id'] ?>" class="text-primary fw-medium text-decoration-none">
                                    Read more <i class="bx bx-right-arrow-alt align-middle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link shadow-sm" href="/?page=<?= $i ?><?= isset($searchQuery) ? '&q='.urlencode($searchQuery) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <!-- Sidebar Content (Right Side) -->
    <div class="col-lg-4">
        
        <!-- Search Widget -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-4">
                <h5 class="card-title mb-3 fw-bold">Search Posts</h5>
                <form action="/search" method="GET" class="d-flex">
                    <input type="text" name="q" class="form-control me-2 border-0 shadow-sm" placeholder="Search..." value="<?= htmlspecialchars($searchQuery ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Categories Widget -->
        <div class="card shadow-sm border-0 mb-4">
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
$content = ob_get_clean(); 
require 'layouts/public.php'; 
?>