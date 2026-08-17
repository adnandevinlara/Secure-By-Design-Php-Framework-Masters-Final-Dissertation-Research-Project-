<?php ob_start(); ?>

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-warning"><i class="bx bx-shield-quarter me-1"></i> Security Audit Dashboard</h4>
        </div>
    </div>
</div>

<!-- Security Matrix Accordion -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion shadow-sm" id="securityAccordion">
            <div class="accordion-item border-warning border-2">
                <h2 class="accordion-header" id="headingSecurity">
                    <button class="accordion-button collapsed bg-warning-subtle text-warning fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="bx bx-check-shield me-2 fs-5"></i> Active Security Controls & ASVS Matrix (Expand to View)
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-warning"><i class="bx bx-check-circle me-1"></i> Audit Logging:</span> 
                                ASVS V7.1: Centralized log aggregation displaying immutable security events for forensic analysis.
                            </li>
                            <li class="list-group-item px-0">
                                <span class="fw-bold text-warning"><i class="bx bx-check-circle me-1"></i> Active Defense:</span> 
                                ASVS V11.1: Administrators can trace attacking IPs and deploy routing-layer blocks directly from the audit trail.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="/security-logs" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Event Type</label>
                <input type="text" name="type" class="form-control form-control-sm" placeholder="e.g., CSRF Violation" value="<?= htmlspecialchars($filters['type'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold text-muted small">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="High" <?= ($filters['severity'] ?? '') === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= ($filters['severity'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= ($filters['severity'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">User</label>
                <input type="text" name="user" class="form-control form-control-sm" placeholder="Username or Guest" value="<?= htmlspecialchars($filters['user'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Security Audit Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-nowrap table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Event</th>
                        <th>Severity</th>
                        <th>User</th>
                        <th>IP (Click to Block)</th>
                        <th>URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-muted"><small><?= htmlspecialchars($log['time']) ?></small></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($log['event']) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if (stripos($log['severity'], 'High') !== false) $badge = 'bg-danger';
                                        elseif (stripos($log['severity'], 'Medium') !== false) $badge = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($log['severity']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($log['user']) ?></td>
                                <td>
                                    <a href="#" class="text-danger fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#blockIpModal" data-ip="<?= htmlspecialchars($log['ip']) ?>" onclick="setBlockIp(this.getAttribute('data-ip'))">
                                        <i class="bx bx-block me-1"></i><?= htmlspecialchars($log['ip']) ?>
                                    </a>
                                </td>
                                <td class="text-muted"><small><?= htmlspecialchars($log['url']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bx bx-shield-check display-4 text-success mb-2 d-block"></i>
                                No matching security events found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- IP Block Confirmation Modal -->
<div class="modal fade" id="blockIpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-6"><i class="bx bx-error-triangle me-1"></i> Block IP Address</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1">Block all traffic from:</p>
                <h4 id="displayIpToBlock" class="fw-bold text-danger mb-3"></h4>
                <p class="small text-muted mb-0">This drops future requests at the routing layer.</p>
            </div>
            <div class="modal-footer bg-light">
                <form method="POST" action="/ip/block" class="w-100 d-flex justify-content-between m-0">
                    <?= \Core\Security\Csrf::getFormField(); ?>
                    <input type="hidden" name="ip_address" id="inputIpToBlock" value="">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Block</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function setBlockIp(ip) {
        document.getElementById('displayIpToBlock').innerText = ip;
        document.getElementById('inputIpToBlock').value = ip;
    }
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>