<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18"><i class="bx bx-comment-detail me-2"></i>Comments Moderation</h4>
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
        <form method="GET" action="/comments" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Comment Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            
            <!-- 🆕 NEW: User Type Dropdown Filter -->
            <div class="col-md-2">
                <label class="form-label">User Type</label>
                <select name="role" class="form-select">
                    <option value="">All Users</option>
                    <option value="super_admin" <?= ($filters['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    <option value="sub_admin" <?= ($filters['role'] ?? '') === 'sub_admin' ? 'selected' : '' ?>>Sub Admin</option>
                    <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>Registered User</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2"><i class="bx bx-search me-1"></i> Search</button>
                <a href="/comments" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- 2. Display All Comments -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive" style="min-height: 300px;">
            <table class="table align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Post Title</th>
                        <th>Comment Content</th>
                        <th>Replied?</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td>
                                    <span class="fw-medium"><?= htmlspecialchars($comment['author'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></span>
                                    <br><small class="text-muted"><?= ucfirst(htmlspecialchars($comment['commenter_role'] ?? 'Guest', ENT_QUOTES, 'UTF-8')) ?></small>
                                </td>
                                <td>
                                    <?php if ($comment['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success py-1 px-2">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning py-1 px-2">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Truncated Content -->
                                <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($comment['post_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $comment['is_replied'] ? '<span class="text-success fw-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                                <td><?= htmlspecialchars(date('M j, Y', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            
                                            <li>
                                                <a class="dropdown-item text-primary fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#viewModal<?= $comment['id'] ?>">
                                                    <i class="bx bx-expand-alt me-1"></i> View & Reply
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item <?= $comment['status'] === 'approved' ? 'text-warning' : 'text-success' ?>" href="#" data-bs-toggle="modal" data-bs-target="#statusModal<?= $comment['id'] ?>">
                                                    <i class="bx <?= $comment['status'] === 'approved' ? 'bx-hide' : 'bx-show' ?> me-1"></i> 
                                                    <?= $comment['status'] === 'approved' ? 'Hide (Set Pending)' : 'Show (Approve)' ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $comment['id'] ?>">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewModal<?= $comment['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light border-bottom">
                                            <h5 class="modal-title fw-bold"><i class="bx bx-message-rounded-dots text-primary me-2"></i>Comment Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        
                                        <div class="modal-body text-wrap p-4">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <span class="fw-bold text-muted small text-uppercase">Post Title</span>
                                                    <h6 class="fw-bold mt-1 text-primary"><?= htmlspecialchars($comment['post_title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                                </div>
                                                <div class="col-md-6 text-md-end">
                                                    <span class="fw-bold text-muted small text-uppercase">Submission Date</span>
                                                    <p class="mb-0 fw-medium"><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            </div>

                                            <div class="mb-4 bg-light p-4 rounded border-start border-primary border-4 shadow-sm">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-2">
                                                        <?= strtoupper(substr(htmlspecialchars($comment['author']), 0, 1)) ?>
                                                    </div>
                                                    <span class="fw-bold fs-5"><?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?> wrote:</span>
                                                </div>
                                                <p class="mb-0 fs-5 text-secondary" style="line-height: 1.6;">
                                                    <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                </p>
                                            </div>
                                            
                                            <hr class="my-4">
                                            
                                            <!-- The Reply Form -->
                                            <form action="/comment/reply" method="POST">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="hidden" name="post_id" value="<?= $comment['post_id'] ?>">
                                                
                                                <div class="mb-3 text-start">
                                                    <label class="form-label fw-bold">Write a Reply</label>
                                                    <textarea name="reply_content" class="form-control bg-light" rows="4" required placeholder="Type your response to <?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?> here..."></textarea>
                                                </div>
                                                
                                                <div class="text-end mt-4">
                                                    <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">Close Window</button>
                                                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-send me-1"></i> Send Reply</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Modal (Unchanged) -->
                            <div class="modal fade" id="statusModal<?= $comment['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Change Comment Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?= $comment['status'] === 'approved' ? 'Are you sure you want to hide this comment? It will no longer appear on your post.' : 'Are you sure you want to approve this comment? It will be publicly visible.' ?>
                                        </div>
                                        <div class="modal-footer">
                                            <form action="/comments/status" method="POST">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $comment['status'] === 'approved' ? 'pending' : 'approved' ?>">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">No, Cancel</button>
                                                <button type="submit" class="btn btn-primary">Yes, Confirm</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal (Unchanged) -->
                            <div class="modal fade" id="deleteModal<?= $comment['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Delete Comment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete this comment permanently? This action cannot be undone.
                                        </div>
                                        <div class="modal-footer">
                                            <form action="/comments/delete" method="POST">
                                                <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
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
                            <td colspan="7" class="text-center text-muted py-4">No comments found matching your criteria.</td>
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