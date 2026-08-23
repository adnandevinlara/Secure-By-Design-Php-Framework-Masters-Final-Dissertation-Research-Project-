<?php ob_start(); ?>

<!-- Security Matrix Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="securityAccordion">
            <div class="accordion-item border-success border-2">
                <h2 class="accordion-header" id="headingSecurity">
                    <button class="accordion-button collapsed bg-success-subtle text-success fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="bx bx-shield-quarter me-2 fs-5"></i> Active Security Controls & ASVS Matrix (Expand to View)
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-success"><i class="bx bx-check-circle me-1"></i> Administrative Access:</span> 
                                ASVS V4.1: Strictly enforces Administrator-only access to this dashboard.
                            </li>
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-success"><i class="bx bx-check-circle me-1"></i> Content Security Policy:</span> 
                                ASVS V14.4: Strict CSP enforced, safely rendering the StarCode Kh template assets.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard Overview</h4>
        </div>
    </div>
</div>
<!-- End Page Title -->

<!-- Stats Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid shadow-sm">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Total Users</p>
                        <h4 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                            <span class="avatar-title"><i class="bx bx-group font-size-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid shadow-sm">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Total Posts</p>
                        <h4 class="mb-0"><?= $stats['total_posts'] ?? 0 ?></h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="avatar-sm rounded-circle bg-success mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-success"><i class="bx bx-file font-size-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid shadow-sm">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Total Comments</p>
                        <h4 class="mb-0"><?= $stats['total_comments'] ?? 0 ?></h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="avatar-sm rounded-circle bg-info mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-info"><i class="bx bx-chat font-size-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-danger border border-1 shadow-sm">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Security Alerts</p>
                        <h4 class="mb-0 text-danger"><?= $stats['total_alerts'] ?? 0 ?></h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="avatar-sm rounded-circle bg-danger mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-danger"><i class="bx bx-shield-quarter font-size-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="row">
    <!-- Recent Posts Feed -->
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="bx bx-list-ul me-1"></i> Recent Posts</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <!-- SECURE: Neutralizing Stored XSS Payloads on output -->
                                        <td class="fw-medium text-truncate" style="max-width: 150px;"><?= htmlspecialchars($post['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td class="text-center text-muted py-3">No recent posts.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Comments Feed -->
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="bx bx-comment-detail me-1"></i> Recent Comments</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <tbody>
                            <?php if (!empty($recent_comments)): ?>
                                <?php foreach ($recent_comments as $comment): ?>
                                    <tr>
                                        <td class="fw-medium text-truncate" style="max-width: 150px;"><?= htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted font-size-12"><?= htmlspecialchars($comment['author'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td class="text-center text-muted py-3" colspan="2">No recent comments.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Security Events Feed -->
    <div class="col-xl-4 col-lg-12 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title text-danger mb-4"><i class="bx bx-shield me-1"></i> Security Events</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <tbody>
                            <?php if (!empty($recent_logs)): ?>
                                <?php foreach ($recent_logs as $log): ?>
                                    <tr>
                                        <td class="fw-medium text-danger text-truncate" style="max-width: 150px;"><?= htmlspecialchars($log['event_type'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted font-size-12"><?= htmlspecialchars($log['ip_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td class="text-center text-muted py-3" colspan="2">No recent security events.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Capture the output and inject it into the master layout
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>