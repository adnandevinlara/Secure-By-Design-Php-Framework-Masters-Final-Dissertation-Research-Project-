<?php

namespace Core\Middleware;

use Core\Security\Csrf;

class CsrfMiddleware
{
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // We only check state-changing requests (POST, PUT, DELETE)
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            
            // Look for the token in the POST data or HTTP headers
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!Csrf::verifyToken($token)) {
                // If validation fails, block the request immediately
                http_response_code(403);
                die("Security Exception: CSRF Token Validation Failed. Request Blocked.");
            }
        }
    }
}