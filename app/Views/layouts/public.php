<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="utf-8"/>
    <title><?= htmlspecialchars($title ?? 'Secure Blog') ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    
    <!-- Bootstrap Css -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Icons Css -->
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css"/>
    <!-- App Css-->
    <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css"/>
    
    <style>
        body { background-color: #f8f9fa; }
        .public-navbar { background-color: #ffffff; border-bottom: 1px solid #e9ecef; }
    </style>
</head>

<body>
    <!-- Top Navigation for UnAuth Pages (Adnan's Point #4) -->
    <nav class="navbar navbar-expand-lg public-navbar py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="/">
                <i class="bx bx-shield-quarter"></i> Secure Blog
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto fw-medium">
                    <li class="nav-item"><a class="nav-link text-dark" href="/">Latest Posts</a></li>
                    
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- If they are logged in, show a link back to the Dashboard -->
                        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="/dashboard">My Dashboard</a></li>
                    <?php else: ?>
                        <!-- UnAuth Links -->
                        <li class="nav-item ms-3"><a class="btn btn-outline-primary px-4" href="/login">Login</a></li>
                        <li class="nav-item ms-2"><a class="btn btn-primary px-4" href="/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area (Blog Posts, Login, Register will be injected here) -->
    <div class="container py-5">
        <?= $content ?? '' ?>
    </div>

    <!-- Simple Public Footer -->
    <footer class="bg-white border-top py-4 mt-5">
        <div class="container text-center text-muted">
            <p class="mb-0"><?= date('Y') ?> © Secure-by-Design PHP Framework Prototype.</p>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>