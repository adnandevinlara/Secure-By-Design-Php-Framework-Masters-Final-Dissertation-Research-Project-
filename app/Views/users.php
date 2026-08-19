<?php ob_start(); ?>

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-info"><i class="bx bx-user-check me-1"></i> Manage Users</h4>
        </div>
    </div>
</div>

<!-- Security Matrix Accordion -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="securityAccordion">
            <div class="accordion-item border-info border-2">
                <h2 class="accordion-header" id="headingSecurity">
                    <button class="accordion-button collapsed bg-info-subtle text-info fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="bx bx-check-shield me-2 fs-5"></i> Active Security Controls & ASVS Matrix (Expand to View)
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-info"><i class="bx bx-check-circle me-1"></i> Role-Based Access Control (RBAC):</span> 
                                ASVS V4.1: Strictly enforces Administrator-only access. Any unauthorized role attempting to view this endpoint is intercepted, logged, and blocked by the AdminMiddleware.
                            </li>
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-info"><i class="bx bx-check-circle me-1"></i> Data Minimization:</span> 
                                ASVS V1.8: The data retrieval query actively excludes sensitive authentication hashes from memory, ensuring they are never accidentally exposed to the view layer.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>UID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= htmlspecialchars($user['id']) ?></td>
                                        <td class="fw-bold text-dark">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-16">
                                                        <?= strtoupper(substr(htmlspecialchars($user['username']), 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <?= htmlspecialchars($user['username']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'Admin' || $user['role'] === 'Administrator'): ?>
                                                <span class="badge bg-success font-size-12"><i class="bx bx-shield me-1"></i><?= htmlspecialchars($user['role']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary font-size-12"><?= htmlspecialchars($user['role'] ?? 'Standard') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted"><small><?= htmlspecialchars($user['created_at']) ?></small></td>
                                        <td class="text-end">
                                            <!-- Action buttons for the next phase -->
                                            <form method="POST" action="/users/delete" class="d-inline m-0" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete this user?');">
                                                <?= \Core\Security\Csrf::getFormField(); ?>
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke Access">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No users found in the database.</td>
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