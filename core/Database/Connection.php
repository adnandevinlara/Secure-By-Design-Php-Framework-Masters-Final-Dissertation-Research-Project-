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
                
                // Automatically create the users table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    role TEXT DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // Automatically create the categories table
                self::$instance->exec("CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // self::$instance->exec("DROP TABLE IF EXISTS comments");

                // Automatically create the updated comments table (with Adnan's new fields)
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

                // 3. Create the POSTS table for the CMS
                self::$instance->exec("CREATE TABLE IF NOT EXISTS posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    category_id INTEGER NULL,
                    content TEXT NOT NULL,
                    author_id INTEGER NOT NULL,
                    status TEXT DEFAULT 'published',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // 4. Create the PASSWORD RESETS table for OTPs
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