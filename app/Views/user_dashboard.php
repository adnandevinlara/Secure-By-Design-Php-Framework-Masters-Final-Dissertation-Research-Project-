<?php ob_start(); ?>

<!-- Section A: Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-primary text-white" style="background: linear-gradient(45deg, #0d6efd, #0dcaf0);">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-2">Welcome, <?= htmlspecialchars($user['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mb-0 fs-5">Manage your profile and interact with the Secure Blog.</p>
                <div class="mt-3 opacity-75 fw-medium">
                    <i class="bx bx-calendar me-1"></i> <?= date('l, F j, Y') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section B: Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 text-center h-100 py-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">My Posts</h6>
                <h2 class="fw-bold text-dark mb-0"><?= htmlspecialchars($stats['user_posts'] ?? '0') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 text-center h-100 py-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Total Comments</h6>
                <h2 class="fw-bold text-info mb-0"><?= htmlspecialchars($stats['user_comments'] ?? '0') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 text-center h-100 py-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Approved Comments</h6>
                <h2 class="fw-bold text-success mb-0"><?= htmlspecialchars($stats['approved_comments'] ?? '0') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 text-center h-100 py-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Pending Comments</h6>
                <h2 class="fw-bold text-warning mb-0"><?= htmlspecialchars($stats['pending_comments'] ?? '0') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
        <div class="card shadow-sm border-0 text-center h-100 py-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Account Status</h6>
                <h5 class="fw-bold text-success mt-3 mb-0"><i class="bx bx-check-circle me-1"></i> ACTIVE</h5>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <!-- Section C: My Recent Comments -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Pending Comments (Needs Moderation)</h5>
            </div>
            <div class="card-body p-0">
                <!-- IMPORTANT: overflow: visible and min-height added to prevent cramped tables! -->
                <div class="table-responsive" style="min-height: 300px; overflow: visible;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Post</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pending_comments)): ?>
                                <?php foreach ($pending_comments as $comment): ?>
                                    <tr>
                                        <td class="text-truncate fw-medium" style="max-width: 130px;"><?= htmlspecialchars($comment['post_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-truncate text-muted" style="max-width: 150px;"><?= htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-warning-subtle text-warning py-1 px-2">Pending</span></td>
                                        <td class="text-muted small"><?= date('d M', strtotime($comment['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bx bx-check-double fs-2 text-success mb-2 d-block"></i>
                                        You're all caught up! No pending comments.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($pending_comments)): ?>
                    <div class="card-footer bg-white border-top text-center py-3">
                        <a href="/comments?status=pending" class="text-primary fw-medium text-decoration-none">View All Pending Comments <i class="bx bx-right-arrow-alt"></i></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Section D: Chart (Comments this week) -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Engagement (Past 7 Days)</h5>
            </div>
            <!-- Added a wrapper to safely constrain the Chart.js canvas -->
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="position: relative; height: 350px;">
                <canvas id="commentsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Section E: Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-uppercase text-muted">Quick Actions</h5>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/post/create" class="btn btn-primary btn-lg shadow-sm px-4"><i class="bx bx-edit me-2"></i> Write New Post</a>
                    <a href="/posts" class="btn btn-outline-primary btn-lg shadow-sm px-4"><i class="bx bx-list-ul me-2"></i> Manage My Posts</a>
                    <a href="/profile" class="btn btn-outline-info btn-lg shadow-sm px-4"><i class="bx bx-user me-2"></i> My Profile</a>
                    <a href="/change-password" class="btn btn-outline-warning btn-lg shadow-sm px-4"><i class="bx bx-lock-alt me-2"></i> Change Password</a>
                    <a href="/logout" class="btn btn-danger btn-lg shadow-sm px-4 ms-md-auto"><i class="bx bx-log-out me-2"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for Section D -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('commentsChart');
    
    // Safety check to ensure canvas exists before initializing
    if(ctx) {
        new Chart(ctx, {
            type: 'line', // Swapped to line chart based on your previous code structure preference!
            data: {
                labels: <?= $chartLabels ?? "['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" ?>,
                datasets: [{
                    label: 'Comments Received',
                    data: <?= $chartData ?? json_encode($chart_data ?? [0,0,0,0,0,0,0]) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    tension: 0.4, // Adds that smooth curve!
                    fill: true,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>