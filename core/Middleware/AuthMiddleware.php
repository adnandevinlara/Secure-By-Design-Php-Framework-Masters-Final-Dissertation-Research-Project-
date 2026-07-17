<?php

namespace Core\Middleware;

use Core\Http\Session;

class AuthMiddleware
{
    /**
     * Protects routes by requiring a valid user session.
     */
    public static function handle(): void
    {
        // If there is no user_id in the session, they are not logged in
        if (!Session::get('user_id')) {
            Session::set('test_auth_status', 'Security Exception: You must be logged in to view this page.');
            header('Location: /login');
            exit; // Immediately stop script execution
        }
    }
}