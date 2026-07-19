<?php

namespace App\Models;

use Core\Database\ORM;
use PDOException;

class User
{
    public static function create(string $username, string $email, string $password): bool
    {
        $orm = new ORM('users');
        
        try {
            // Using the strictly parameterized insert method
            return $orm->insert([
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);
        } catch (PDOException $e) {
            // If the email already exists, SQLite will throw a constraint violation
            return false;
        }
    }

    public static function findByEmail(string $email): array|false
    {
        $orm = new ORM('users');
        
        // Using strict method chaining: ->where()->first()
        return $orm->where('email', $email)->first();
    }
}