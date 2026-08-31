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
                
                // 1. Create the USERS table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    role TEXT DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
                
                try {
                    self::$instance->exec("ALTER TABLE users ADD COLUMN permissions TEXT DEFAULT NULL");
                } catch (\PDOException $e) {}

                // 2. Create the CATEGORIES table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                try {
                    self::$instance->exec("ALTER TABLE categories ADD COLUMN status TEXT DEFAULT 'show'");
                } catch (\PDOException $e) {}

                // Create SECURITY_LOGS table for the Admin Dashboard
                self::$instance->exec("CREATE TABLE IF NOT EXISTS security_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    event_type TEXT NOT NULL,
                    ip_address TEXT NOT NULL,
                    user TEXT NOT NULL,
                    details TEXT,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                try {
                    self::$instance->exec("ALTER TABLE security_logs ADD COLUMN user TEXT DEFAULT 'Unknown'");
                } catch (\PDOException $e) {}

                try {
                    self::$instance->exec("ALTER TABLE security_logs ADD COLUMN details TEXT NULL");
                } catch (\PDOException $e) {}

                // 3. Create the updated COMMENTS table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    author_id INTEGER NOT NULL,
                    author TEXT NOT NULL,
                    content TEXT NOT NULL,
                    status TEXT DEFAULT 'pending',
                    is_replied INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // 4. Create the POSTS table (Now includes banner_image natively)
                self::$instance->exec("CREATE TABLE IF NOT EXISTS posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    category_id INTEGER NULL,
                    content TEXT NOT NULL,
                    author_id INTEGER NOT NULL,
                    status TEXT DEFAULT 'published',
                    banner_image TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // Safely attempt to add the new columns to existing SQLite databases
                try {
                    self::$instance->exec("ALTER TABLE posts ADD COLUMN status TEXT DEFAULT 'published'");
                } catch (\PDOException $e) {}

                try {
                    self::$instance->exec("ALTER TABLE posts ADD COLUMN banner_image TEXT NULL");
                } catch (\PDOException $e) {}

                // 5. Create the PASSWORD RESETS table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS password_resets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email TEXT NOT NULL,
                    otp TEXT NOT NULL,
                    expires_at DATETIME NOT NULL
                )");

            } catch (PDOException $e) {
                die("Security Exception: Database Connection failed. " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
}