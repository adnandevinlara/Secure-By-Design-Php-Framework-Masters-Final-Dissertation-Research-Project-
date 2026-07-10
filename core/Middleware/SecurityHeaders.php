<?php

namespace Core\Middleware;

class SecurityHeaders
{
    public static function apply(): void
    {
        // 1. Prevent Clickjacking (Stops other sites from embedding your site in an iframe)
        header('X-Frame-Options: DENY');
        
        // 2. Prevent MIME-sniffing (Stops browsers from trying to guess file types)
        header('X-Content-Type-Options: nosniff');
        
        // 3. Strict Transport Security (HSTS - Forces browsers to only use HTTPS in production)
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        // 4. Cross-Site Scripting Protection (Fallback for older browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // 5. Content Security Policy (CSP - Strictly controls where scripts/styles can load from)
        // Note: We are allowing 'unsafe-inline' for styles right now so our dashboard CSS works.
        header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline';");
    }
}