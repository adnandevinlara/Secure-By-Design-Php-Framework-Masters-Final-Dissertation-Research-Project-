<?php

namespace Core\Middleware;

class GuestMiddleware
{
    public static function handle(): void
    {
        // If the user is already logged in, they have no business being on the Login/Register pages.
        // Redirect them straight to their secure dashboard.
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }
    }
}