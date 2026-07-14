<?php

namespace Core\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Using SQLite for a seamless, secure local database
            $dbPath = __DIR__ . '/../../secure_app.sqlite';
            
            try {
                self::$instance = new PDO("sqlite:" . $dbPath);
                
                // Enforce strict error handling and security modes
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Automatically create the users table if it doesn't exist yet
                self::$instance->exec("CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            } catch (PDOException $e) {
                die("Security Exception: Database Connection failed. " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
}