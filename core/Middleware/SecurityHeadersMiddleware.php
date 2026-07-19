<?php

namespace Core\Middleware;

class SecurityHeadersMiddleware
{
    // Injects strict security headers into the HTTP response.
    public static function handle(): void
    {
        // 1. X-Frame-Options: Prevents Clickjacking by forbidding the site from being rendered in an iframe.
        header('X-Frame-Options: DENY');

        // 2. X-Content-Type-Options: Prevents MIME-sniffing, forcing the browser to stick to the declared content type.
        header('X-Content-Type-Options: nosniff');

        // 3. Strict-Transport-Security (HSTS): Forces browsers to use secure HTTPS connections (ignored on localhost, but required for production).
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // 4. Content Security Policy (CSP): Severely restricts where resources (scripts, images, styles) can be loaded from to mitigate XSS.
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");

        // 5. Referrer-Policy: Controls how much referrer information is included with requests.
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}