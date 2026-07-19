<?php

namespace Core\Middleware;

use Core\Security\Csrf;
use Core\Security\Logger;

class CsrfMiddleware
{
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!Csrf::verifyToken($token)) {
                // 🚨 Log the active CSRF attack
                \Core\Security\Logger::log('CRITICAL', 'CSRF_BLOCKED', 'A state-changing request failed token validation and was intercepted.');
                
                http_response_code(403);
                die("Security Exception: CSRF Token Validation Failed. Request Blocked.");
            }
        }
    }
}