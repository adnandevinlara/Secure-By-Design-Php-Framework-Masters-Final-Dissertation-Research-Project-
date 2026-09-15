<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18"><i class="bx bx-file me-2"></i>Blog Posts Management</h4>
            <div class="page-title-right">
                <a href="/post/create" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Create New Post</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success shadow-sm border-0"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- 1. Search Form On Top -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="/posts" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Title</label>
                <input type="text" name="title" class="form-control" placeholder="Search keywords..." value="<?= htmlspecialchars($filters['title'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <!-- Categories will be populated dynamically by the controller -->
                    <?php foreach ($categories ?? [] as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="hidden" <?= ($filters['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
            <!-- User Type Filter -->
            <div class="col-md-2">
                <label class="form-label">User Type</label>
                <select name="role" class="form-select">
                    <option value="">All User Types</option>
                    <option value="super_admin" <?= ($filters['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    <option value="sub_admin" <?= ($filters['role'] ?? '') === 'sub_admin' ? 'selected' : '' ?>>Sub Admin</option>
                    <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>Registered User</option>
                </select>
            </div>

            <!-- Author Name Filter -->
            <div class="col-md-2">
                <label class="form-label">Author</label>
                <select name="author_id" class="form-select">
                    <option value="">All Authors</option>
                    <?php foreach ($authors ?? [] as $auth): ?>
                        <option value="<?= $auth['id'] ?>" <?= ($filters['author_id'] ?? '') == $auth['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($auth['username'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES) ?>">
            </div>
            <!-- End Date Filter -->
            <div class="col-md-2">
                <label class="form-label fw-bold text-muted small">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2"><i class="bx bx-search me-1"></i> Search</button>
                <a href="/posts" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- 2. Display All Blog Posts -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive" style="min-height: 300px;">
            <table class="table align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td class="fw-medium text-truncate" style="max-width: 250px;">
                                    <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><span class="badge bg-info-subtle text-info py-1 px-2"><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <span class="fw-medium"><?= htmlspecialchars($post['author_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                                    <br><small class="text-muted"><?= ucfirst(htmlspecialchars($post['author_role'] ?? 'user', ENT_QUOTES, 'UTF-8')) ?></small>
                                </td>
                                <td>
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="badge bg-success-subtle text-success py-1 px-2">Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary py-1 px-2">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('M j, Y', strtotime($post['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <!-- View Public Post -->
                                                <a class="dropdown-item text-primary" href="/post/view?id=<?= $post['id'] ?>" target="_blank">
                                                    <i class="bx bx-show me-1"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <!-- Edit Post -->
                                                <a class="dropdown-item text-info" href="/post/edit?id=<?= $post['id'] ?>">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <!-- Hide/Show Post Modal Trigger -->
                                                <a class="dropdown-item <?= $post['status'] === 'published' ? 'text-warning' : 'text-success' ?>" href="#" data-bs-toggle="modal" data-bs-target="#statusModal<?= $post['id'] ?>">
                                                    <i class="bx <?= $post['status'] === 'published' ? 'bx-hide' : 'bx-bulb' ?> me-1"></i> 
                                                    <?= $post['status'] === 'published' ? 'Hide Post' : 'Publish Post' ?>
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <!-- Delete Post Modal Trigger -->
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $post['id'] ?>">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Status Modal -->
                            <div class="modal fade" id="statusModal<?= $post['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Change Post Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?= $post['status'] === 'published' ? 'Are you sure you want to hide this post? It will no longer be visible to the public.' : 'Are you sure you want to publish this post again? It will be publicly visible.' ?>
                                        </div>
                                        <div class="modal-footer">
                                            <form action="/post/status" method="POST">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $post['status'] === 'published' ? 'hidden' : 'published' ?>">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">No, Cancel</button>
                                                <button type="submit" class="btn btn-primary">Yes, Confirm</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal<?= $post['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Delete Post</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete this post permanently? This action cannot be undone.
                                        </div>
                                        <div class="modal-footer">
                                            <form action="/post/delete" method="POST">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">No, Cancel</button>
                                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No posts found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>