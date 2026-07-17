<?php

namespace Core\Security;

use Core\Http\Session;

class Throttler
{
    // Configure the strictness of the firewall
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 60; // Penalty in seconds

    /**
     * Checks if the user is currently locked out.
     */
    public static function isAllowed(string $key): bool
    {
        $lockoutExpiration = Session::get($key . '_lockout', 0);
        
        // If the current server time is less than the expiration, they are still blocked
        if (time() < $lockoutExpiration) {
            return false;
        }

        return true;
    }

    // Records a failed attempt and triggers a lockout if the max is reached.
    public static function recordFailure(string $key): void
    {
        $attempts = Session::get($key . '_attempts', 0) + 1;
        Session::set($key . '_attempts', $attempts);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::set($key . '_lockout', time() + self::LOCKOUT_TIME);
            Session::set($key . '_attempts', 0); 
            
            // 🚨 Log the brute-force lockout
            \Core\Security\Logger::log('BRUTE_FORCE_LOCKOUT', "User tracking key $key was locked out for exceeding maximum login attempts.");
        }
    }

    // Clears the penalty record upon a successful login.
    public static function clear(string $key): void
    {
        Session::set($key . '_attempts', 0);
        Session::set($key . '_lockout', 0);
    }

    // Calculates exactly how many seconds are left in the penalty box.
    public static function getRemainingLockoutSeconds(string $key): int
    {
        return max(0, Session::get($key . '_lockout', 0) - time());
    }
}