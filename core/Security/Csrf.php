<?php

namespace Core\Security;

use Core\Http\Session;

class Csrf
{
    /**
     * Generates a cryptographically secure token and stores it in the session.
     */
    public static function generateToken(): string
    {
        // If a token doesn't exist for this session, create one
        if (!Session::get('csrf_token')) {
            // random_bytes() generates secure, unpredictable values
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        
        return Session::get('csrf_token');
    }

    /**
     * Validates an incoming token against the stored session token.
     */
    public static function verifyToken(?string $token): bool
    {
        $storedToken = Session::get('csrf_token');
        
        if (!$storedToken || !$token) {
            return false;
        }

        // hash_equals prevents timing attacks during string comparison
        return hash_equals($storedToken, $token);
    }

    public static function getFormField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}