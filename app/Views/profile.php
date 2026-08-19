<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-user me-1"></i> My Profile</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card overflow-hidden shadow-sm border-0">
            <div class="bg-primary bg-soft">
                <div class="row">
                    <div class="col-7">
                        <div class="text-primary p-3">
                            <h5 class="text-primary">Welcome Back!</h5>
                            <p>Secure CMS Dashboard</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="avatar-md profile-user-wid mb-4" style="margin-top: -36px;">
                            <span class="avatar-title rounded-circle bg-primary text-white font-size-24 border border-3 border-white shadow-sm">
                                <?= strtoupper(substr(htmlspecialchars($user['username']), 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="font-size-15 text-truncate"><?= htmlspecialchars($user['username']) ?></h5>
                        <p class="text-muted mb-0 text-truncate"><?= htmlspecialchars($user['role']) ?></p>
                    </div>

                    <div class="col-sm-8">
                        <div class="pt-4">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="font-size-15">Email Address</h5>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title mb-4">Account Security Information</h4>
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <tbody>
                            <tr>
                                <th scope="row">Password Hashing Algorithm:</th>
                                <td><span class="badge bg-success">Argon2id (ASVS V2.1)</span></td>
                            </tr>
                            <tr>
                                <th scope="row">Session Status:</th>
                                <td><span class="badge bg-success">Active & Encrypted</span></td>
                            </tr>
                            <tr>
                                <th scope="row">Role-Based Access:</th>
                                <td><?= htmlspecialchars($user['role']) ?> Privileges Granted</td>
                            </tr>
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