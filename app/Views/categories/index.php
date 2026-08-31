<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-purchase-tag-alt me-1"></i> Manage Categories</h4>
            <div class="page-title-right">
                <!-- Point #19: Link to the separate creation form -->
                <a href="/category/create" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add New Category</a>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Point #18: Search Form On Top -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="/categories" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search Category Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Technology" value="<?= htmlspecialchars($filters['name'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="show" <?= ($filters['status'] ?? '') === 'show' ? 'selected' : '' ?>>Show (Active)</option>
                    <option value="hide" <?= ($filters['status'] ?? '') === 'hide' ? 'selected' : '' ?>>Hide (Draft)</option>
                </select>
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-primary me-2"><i class="bx bx-search me-1"></i> Search</button>
                <a href="/categories" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Point #18: Categories Data Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive" style="min-height: 300px; overflow: visible;">
            <table class="table align-middle table-nowrap table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Category Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-bold text-dark"><span class="badge bg-info-subtle text-info font-size-13"><?= htmlspecialchars($cat['name']) ?></span></td>
                                <td>
                                    <?php if ($cat['status'] === 'show'): ?>
                                        <span class="badge bg-success-subtle text-success py-1 px-2">Show</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning py-1 px-2">Hide</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><small><?= htmlspecialchars($cat['created_at']) ?></small></td>
                                <td>
                                    <!-- Action Dropdown for Point #18 (Edit, Status, Delete) -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form action="/category/status" method="POST" class="d-inline">
                                                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                    <input type="hidden" name="status" value="<?= $cat['status'] === 'show' ? 'hide' : 'show' ?>">
                                                    <button type="submit" class="dropdown-item <?= $cat['status'] === 'show' ? 'text-warning' : 'text-success' ?>">
                                                        <i class="bx <?= $cat['status'] === 'show' ? 'bx-hide' : 'bx-show' ?> me-1"></i> 
                                                        <?= $cat['status'] === 'show' ? 'Hide' : 'Show' ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="/category/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this category?');">
                                                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main.php'; 
?>