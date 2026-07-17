<?php

namespace Core\Middleware;

use Core\Http\Session;

class AdminMiddleware
{
    public static function handle(): void
    {
        $role = Session::get('role', 'guest');
        
        if ($role !== 'admin') {
            // Updated to match your exact Logger.php method signature
            \Core\Security\Logger::log('RBAC_VIOLATION', 'A standard user attempted to access an admin-only route.');
            
            Session::set('test_auth_status', 'Security Exception: You do not have administrator clearance.');
            header('Location: /dashboard');
            exit;
        }
    }
}