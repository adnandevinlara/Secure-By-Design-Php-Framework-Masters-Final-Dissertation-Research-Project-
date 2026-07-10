<?php

namespace Core\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    // We make the constructor private so developers cannot create multiple unprotected connections
    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // These credentials match your docker-compose.yml file exactly
            $host = 'db'; 
            $db   = 'secure_cms';
            $user = 'cms_user';
            $pass = 'cmspassword';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // CRITICAL SECURITY FIX: Disable emulated prepares to enforce true parameterized queries at the database level
                PDO::ATTR_EMULATE_PREPARES   => false, 
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // We intentionally mask the actual error message here to prevent Information Disclosure
                die("Critical System Error: Database connection failed. (Secure-by-Design Masking Active)");
            }
        }

        return self::$instance;
    }
}