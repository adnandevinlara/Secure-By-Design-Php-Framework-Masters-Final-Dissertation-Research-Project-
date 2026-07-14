<?php

namespace Core\Http;

class Session
{
    public static function start(): void
    {
        // Only start a session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            
            // ASVS Requirement: Secure Cookie Configurations
            // Prevents JavaScript from reading the session cookie (XSS protection)
            ini_set('session.cookie_httponly', '1');
            
            // Forces the session to only use cookies, preventing URL injection
            ini_set('session.use_only_cookies', '1');
            
            // Prevents the browser from sending the cookie along with cross-site requests (CSRF protection)
            ini_set('session.cookie_samesite', 'Strict');
            
            // Note: In a live production environment, we would also enable 'session.cookie_secure' to force HTTPS.
            // We leave it off here so it works on your local Docker setup.

            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }
}