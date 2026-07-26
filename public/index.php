<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Http\Router;
use Core\Http\Request;
use Core\Http\Response;

// Boot the secure session manager
\Core\Http\Session::start();

// Apply Global Security Headers
\Core\Middleware\SecurityHeadersMiddleware::handle();   

// 1. Initialize the HTTP Lifecycle components
$request = new \Core\Http\Request();
$response = new \Core\Http\Response();

// 2. Pass them into the Router
$router = new \Core\Http\Router($request, $response);

// Apply global security headers to every response
\Core\Middleware\SecurityHeaders::apply();

// Enforce CSRF token validation on all state-changing requests
\Core\Middleware\CsrfMiddleware::handle();

// 3. Define our test routes (Notice they now use $req and $res)
$router->get('/', function (Request $req, Response $res) {
    // We are now safely outputting HTML through our Response handler!
    $res->html("<h1>Welcome to the Secure-by-Design Framework</h1><p>The core routing engine is now successfully using the Request and Response classes.</p>");
});

$router->get('/api/status', function (Request $req, Response $res) {
    // We are now safely outputting JSON through our Response handler!
    $res->json(["status" => "Secure Framework is active", "version" => "1.1"]);
});

$router->get('/api/db-test', function (Request $req, Response $res) {
    try {
        $db = \Core\Database\Connection::getInstance();
        $res->json([
            "status" => "success", 
            "message" => "Database connected securely!"
        ]);
    } catch (\Throwable $e) { // \Throwable catches literally everything in PHP 8
        $res->json([
            "status" => "error", 
            "actual_error" => $e->getMessage(),
            "file_where_it_failed" => basename($e->getFile()),
            "line_number" => $e->getLine()
        ]);
    }
});

$router->get('/api/setup-db', function ($req, $res) {
    $db = \Core\Database\Connection::getInstance();
    
    // 1. Create the USERS table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'Registered',
        created_at DATETIME NOT NULL
    )");

    // 2. Create the SECURITY_LOGS table
    $db->exec("CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        severity VARCHAR(50) NOT NULL,
        user_id INT NULL,
        ip_address VARCHAR(45) NOT NULL,
        request_url VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        timestamp DATETIME NOT NULL
    )");

    // 3. Create the POSTS table for the CMS
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        author_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $res->json(["status" => "success", "message" => "Database tables generated successfully!"]);
});

$router->get('/api/create-admin', function ($req, $res) {
    $userModel = new \App\Models\User();

    // Check if the admin already exists so we don't cause a database duplicate error
    $existingUser = $userModel->findByEmail('admin@securecms.com');
    
    if ($existingUser) {
        $res->json([
            "status" => "info", 
            "message" => "Admin user already exists!",
            "user" => [
                "username" => $existingUser['username'],
                "email" => $existingUser['email'],
                "role" => $existingUser['role'],
                // Notice how the password looks like random gibberish? That is Argon2id working!
                "hash" => $existingUser['password_hash'] 
            ]
        ]);
        return;
    }

    // Securely insert the new admin
    $success = $userModel->create(
        'Adnan Admin', 
        'admin@securecms.com', 
        'SecurePassword123!', 
        'Admin'
    );

    if ($success) {
        $res->json([
            "status" => "success", 
            "message" => "First Administrator account successfully securely created!"
        ]);
    } else {
        $res->json([
            "status" => "error", 
            "message" => "Failed to create the admin account."
        ]);
    }
});

$router->get('/api/test-logger', function ($req, $res) {
    $logger = new \App\Models\SecurityLog();
    
    // Simulate a hacker trying to access the admin panel
    $success = $logger->logEvent(
        'UNAUTHORIZED_ACCESS',
        'High',
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        '/admin/dashboard',
        'Attempted to access admin dashboard without an active session.',
        null // No user ID because they aren't logged in
    );

    if ($success) {
        $res->json(["status" => "success", "message" => "Security violation successfully logged!"]);
    } else {
        $res->json(["status" => "error", "message" => "Failed to write to audit log."]);
    }
});

// Protected Dashboard Route
$router->get('/dashboard', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    // 🔒 The Bouncer: Run the Auth Middleware before showing the page
    \Core\Middleware\AuthMiddleware::handle();
    
    // FIXED: Route to BlogController instead so it fetches your posts!
    $controller = new \App\Controllers\BlogController();
    $controller->index($req, $res);
});

// Logout Route
$router->get('/logout', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\AuthController();
    $controller->logout($req, $res);
});

// Test route for valid form submissions
$router->post('/test-post', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $res->html("Success! The CSRF token was perfectly valid and the request was securely processed.");
});

// Authentication Routes
$router->get('/register', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\AuthController();
    $controller->showRegister($req, $res);
});

$router->post('/register', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\AuthController();
    $controller->processRegister($req, $res);
});

$router->get('/login', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\AuthController();
    $controller->showLogin($req, $res);
});

// Secure Blog Post Submission Route
$router->post('/post/store', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\BlogController();
    $controller->store($req, $res);
});

$router->post('/login', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    $controller = new \App\Controllers\AuthController();
    $controller->processLogin($req, $res);
});

// Admin-Only Route
$router->get('/admin-panel', function (\Core\Http\Request $req, \Core\Http\Response $res) {
    // Run the VIP bouncer
    \Core\Middleware\AdminMiddleware::handle();
    
    $res->html("Welcome to the Admin Panel. You have Super Administrator clearance.");
});

// Temporary route to upgrade a user to Admin

// $router->get('/make-me-admin', function () {
//     $db = \Core\Database\Connection::getInstance();
//     $db->exec("UPDATE users SET role = 'admin' WHERE id = 1");
//     echo "User 1 has been upgraded to Admin! Please log out and log back in.";
// });

$router->dispatch();