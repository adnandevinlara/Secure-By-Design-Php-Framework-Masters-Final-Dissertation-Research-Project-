<?php ob_start(); ?>

<!-- Page Title & Create Button -->
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-file me-1"></i> Blog Posts Management</h4>
        
        <!-- The New Create Post Button -->
        <a href="/post/create" class="btn btn-primary shadow-sm">
            <i class="bx bx-plus me-1"></i> Create New Post
        </a>
    </div>
</div>

<!-- Posts Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Content Snippet</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($post['title'] ?? 'Untitled') ?></td>
                                        
                                        <!-- NEW: Display the dynamically joined Category Name -->
                                        <td>
                                            <span class="badge bg-info-subtle text-info font-size-12">
                                                <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-muted text-truncate" style="max-width: 300px;">
                                            <?= htmlspecialchars(substr($post['content'] ?? '', 0, 50)) ?>...
                                        </td>
                                        <td><span class="badge bg-success-subtle text-success">Published</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No posts found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>