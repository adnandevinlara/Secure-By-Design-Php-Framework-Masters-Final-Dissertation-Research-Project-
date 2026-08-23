<?php ob_start(); ?>

<!-- A. Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-primary-subtle text-primary">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($user['username'] ?? 'User') ?></h4>
                    <p class="mb-0">Manage your profile and interact with the Secure Blog.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6"><i class="bx bx-calendar me-1"></i> <?= date('F j, Y') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- B. Summary Cards -->
<div class="row mb-4">
    <div class="col">
        <div class="card shadow-sm border-0 text-center py-3">
            <h3 class="text-primary fw-bold"><?= htmlspecialchars($stats['user_posts'] ?? '0') ?></h3>
            <span class="text-muted font-size-13">Posts</span>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 text-center py-3">
            <h3 class="text-info fw-bold"><?= htmlspecialchars($stats['user_comments'] ?? '0') ?></h3>
            <span class="text-muted font-size-13">Comments</span>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 text-center py-3">
            <h3 class="text-success fw-bold"><?= htmlspecialchars($stats['approved_comments'] ?? '0') ?></h3>
            <span class="text-muted font-size-13">Approved</span>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 text-center py-3">
            <h3 class="text-warning fw-bold"><?= htmlspecialchars($stats['pending_comments'] ?? '0') ?></h3>
            <span class="text-muted font-size-13">Pending</span>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 text-center py-3">
            <h3 class="text-success fw-bold">ACTIVE</h3>
            <span class="text-muted font-size-13">Account Status</span>
        </div>
    </div>
</div>

<!-- C & D. Recent Comments and Chart Section -->
<div class="row mb-4">
    <!-- C. My Recent Comments (Pending) -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="bx bx-time-five me-1"></i> My Recent Comments (Pending)</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
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
                                        <td class="text-truncate" style="max-width: 120px;"><?= htmlspecialchars($comment['post_title'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($comment['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                                        <td class="text-muted"><?= htmlspecialchars(date('d M', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No pending comments.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Comments Chart -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="bx bx-bar-chart-alt-2 me-1"></i> Comments Received (Current Week)</h5>
                <!-- Canvas for Chart.js Integration -->
                <canvas id="commentsChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- E. Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-4">
                <h5 class="mb-4">Quick Actions</h5>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="/posts" class="btn btn-outline-primary btn-lg px-4"><i class="bx bx-list-ul me-2"></i> Browse Posts</a>
                    <a href="/profile" class="btn btn-outline-info btn-lg px-4"><i class="bx bx-user me-2"></i> My Profile</a>
                    <a href="/change-password" class="btn btn-outline-warning btn-lg px-4"><i class="bx bx-lock-alt me-2"></i> Change Password</a>
                    <a href="/logout" class="btn btn-outline-danger btn-lg px-4"><i class="bx bx-power-off me-2"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script for the Weekly Comments Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('commentsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Comments Received',
                data: <?= json_encode($chart_data ?? [0,0,0,0,0,0,0]) ?>,
                backgroundColor: 'rgba(85, 110, 230, 0.5)',
                borderColor: 'rgba(85, 110, 230, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>