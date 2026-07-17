<?php

namespace Core\Security;

class Validator
{
    /**
     * Sanitizes a standard string by removing tags and encoding special characters.
     */
    public static function sanitizeString(string $data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizes an email address.
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validates that the email is actually in a proper format.
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * ASVS Requirement: Enforce strict password complexity.
     * Minimum 8 characters, at least one uppercase, one lowercase, and one number.
     */
    public static function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
    }
}