<?php
// ==============================================================================
// VERSION B: SECURE ROUTING CONFIGURATION
// ==============================================================================

use Core\Router;
use App\Controllers\AuthController;

$router = new Router();

// Secure Registration Routes
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'processRegister'], ['CsrfMiddleware']);

// Secure Login Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'processLogin'], ['CsrfMiddleware', 'Throttler']);

// Secure Logout Route
$router->post('/logout', [AuthController::class, 'logout'], ['CsrfMiddleware']);