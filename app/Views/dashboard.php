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
                                ASVS V4.1: Enforces Strict Granular Access Control List (ACL) for Dashboard views.
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

<!-- Start Page Title & Phase 3 Quick Links -->
<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-white p-3 shadow-sm rounded">
            <h4 class="mb-sm-0 font-size-18 text-primary">
                <i class="bx bx-home-smile me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>!
            </h4>
            
            <!-- PHASE 3: Quick Links -->
            <div class="page-title-right">
                <a href="/posts/create" class="btn btn-primary btn-sm shadow-sm me-1">
                    <i class="bx bx-plus-circle align-middle"></i> New Post
                </a>
                <a href="/users" class="btn btn-info btn-sm shadow-sm me-1 text-white">
                    <i class="bx bx-group align-middle"></i> Manage Users
                </a>
                <a href="/comments" class="btn btn-warning btn-sm shadow-sm text-dark">
                    <i class="bx bx-message-square-dots align-middle"></i> Comments
                </a>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<!-- Stats Row -->
<div class="row">
    
    <!-- USERS CARD (Requires 'view_users') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_users', $perms))): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card mini-stats-wid shadow-sm h-100">
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
    <?php endif; ?>

    <!-- POSTS CARD (Requires 'view_blogs') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_blogs', $perms))): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card mini-stats-wid shadow-sm h-100">
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
    <?php endif; ?>

    <!-- COMMENTS CARD (Requires 'view_comments') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_comments', $perms))): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card mini-stats-wid shadow-sm h-100">
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
    <?php endif; ?>

    <!-- SECURITY ALERTS CARD (Requires 'view_security') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_security', $perms))): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card mini-stats-wid border-danger border border-1 shadow-sm h-100">
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
    <?php endif; ?>
</div>

<!-- PHASE 3: Activity Chart Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h4 class="card-title mb-4">Site Activity (Comments Last 7 Days)</h4>
                <canvas id="activityChart" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="row">
    
    <!-- Recent Posts Feed (Requires 'view_blogs') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_blogs', $perms))): ?>
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
                                        <td class="fw-medium text-truncate" style="max-width: 150px;">
                                            <?= htmlspecialchars($post['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
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
    <?php endif; ?>

    <!-- Recent Comments Feed (Requires 'view_comments') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_comments', $perms))): ?>
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
                                        <td class="fw-medium text-truncate" style="max-width: 150px;">
                                            <?= htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-muted font-size-12">
                                            <?= htmlspecialchars($comment['author'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
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
    <?php endif; ?>

    <!-- Recent Security Events Feed (Requires 'view_security') -->
    <?php if ($isAdmin || ($isSubAdmin && in_array('view_security', $perms))): ?>
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
                                        <td class="fw-medium text-danger text-truncate" style="max-width: 150px;">
                                            <?= htmlspecialchars($log['event_type'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-muted font-size-12">
                                            <?= htmlspecialchars($log['ip_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
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
    <?php endif; ?>
</div>

<!-- Load Chart.js for Phase 3 Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('activityChart');
        if (ctx) {
            // Parse the JSON data passed securely from the DashboardController
            const labels = <?= $chartLabels ?? '[]' ?>;
            const dataPoints = <?= $chartData ?? '[]' ?>;

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Comments',
                        data: dataPoints,
                        backgroundColor: 'rgba(52, 195, 143, 0.2)', // Soft green
                        borderColor: '#34c38f', // Solid green
                        borderWidth: 2,
                        pointBackgroundColor: '#34c38f',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>

<?php 
// Capture the output and inject it into the master layout
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>