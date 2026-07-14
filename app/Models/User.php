<?php

namespace App\Models;

use Core\Database\Connection;
use PDOException;

class User
{
    /**
     * Securely inserts a new user into the database.
     */
    public static function create(string $username, string $email, string $hashedPassword): bool
    {
        $db = Connection::getInstance();
        
        // ASVS Requirement: Always use Prepared Statements to prevent SQL Injection
        // The parameters (:username, etc.) ensure user input is never treated as executable code
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        
        try {
            return $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            // If the email already exists, SQLite will throw a constraint violation
            return false;
        }
    }
}