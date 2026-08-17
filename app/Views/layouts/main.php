<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="utf-8"/>
    <title><?= htmlspecialchars($title ?? 'Secure CMS | Admin') ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="Secure-by-Design PHP Framework CMS" name="description"/>
    
    <!-- App favicon -->
    <link href="/assets/images/favicon.ico" rel="shortcut icon"/>
    <!-- Bootstrap Css -->
    <link href="/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css"/>
    <!-- Icons Css -->
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css"/>
    <!-- App Css-->
    <link href="/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css"/>
    <!-- App js -->
    <script src="/assets/js/plugin.js"></script>
    
    <style>
        .security-accordion-btn { background-color: #e8f5e9; color: #198754; font-weight: 600; border: 1px solid #c8e6c9; }
    </style>
</head>

<body data-sidebar="dark">
    <!-- Begin page -->
    <div id="layout-wrapper">
        
        <!-- Header -->
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a class="logo logo-dark" href="/dashboard">
                            <span class="logo-sm">
                                <img alt="Logo" height="22" src="/assets/images/logo.svg"/>
                            </span>
                            <span class="logo-lg">
                                <img alt="Logo" height="17" src="/assets/images/logo-dark.png"/>
                            </span>
                        </a>
                        <a class="logo logo-light" href="/dashboard">
                            <span class="logo-sm">
                                <img alt="Logo" height="22" src="/assets/images/logo-light.svg"/>
                            </span>
                            <span class="logo-lg">
                                <span class="fs-4 fw-bold text-white"><i class="bx bx-shield-quarter text-primary"></i> Secure CMS</span>
                            </span>
                        </a>
                    </div>

                    <button class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn" type="button">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center">
                    <!-- User Dropdown & Role -->
                    <div class="dropdown d-inline-block me-3">
                        <span class="badge bg-primary font-size-12">Role: <?= htmlspecialchars($_SESSION['user']['role'] ?? 'Administrator') ?></span>
                    </div>

                    <div class="dropdown d-inline-block">
                        <button aria-expanded="false" aria-haspopup="true" class="btn header-item waves-effect" data-bs-toggle="dropdown" id="page-header-user-dropdown" type="button">
                            <img alt="Header Avatar" class="rounded-circle header-profile-user" src="/assets/images/users/avatar-1.jpg"/>
                            <span class="d-none d-xl-inline-block ms-1"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin User') ?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile"><i class="bx bx-user font-size-16 align-middle me-1"></i> Profile</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="/logout" class="m-0">
                                <?= \Core\Security\Csrf::getFormField(); ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Left Sidebar Navigation -->
        <div class="vertical-menu">
            <div class="h-100" data-simplebar="">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">CMS Navigation</li>

                        <li>
                            <a class="waves-effect" href="/dashboard">
                                <i class="bx bx-home-circle"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a class="waves-effect" href="/posts">
                                <i class="bx bx-file"></i>
                                <span>Blog Posts</span>
                            </a>
                        </li>

                        <li>
                            <a class="waves-effect" href="/categories">
                                <i class="bx bx-purchase-tag-alt"></i>
                                <span>Categories</span>
                            </a>
                        </li>

                        <li>
                            <a class="waves-effect" href="/comments">
                                <i class="bx bx-chat"></i>
                                <span>Comments</span>
                            </a>
                        </li>

                        <li>
                            <a class="waves-effect" href="/users">
                                <i class="bx bx-user-check"></i>
                                <span>Manage Users</span>
                            </a>
                        </li>

                        <li class="menu-title">Security & Forensics</li>

                        <li>
                            <a class="waves-effect text-warning" href="/security-logs">
                                <i class="bx bx-shield-quarter text-warning"></i>
                                <span class="text-warning">Security Audit Logs</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">


                    <!-- Dynamic Page View Injected Here -->
                    <?= $content ?? '' ?>

                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= date('Y') ?> © Secure-by-Design PHP Framework Prototype.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Design & Custom Template by StarCode Kh
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script src="/assets/libs/jquery/jquery.min.js"></script>
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="/assets/libs/node-waves/waves.min.js"></script>
    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>
</html>