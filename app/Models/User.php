<?php

namespace App\Models;

use Core\Database\Model;

class User extends Model
{
    // Tell the base Model class which database table to use
    protected string $table = 'users';

    // Securely find a user for the login process
    public function findByEmail(string $email): ?array
    {
        // Notice we are STILL using strict parameterized queries here
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Securely register a new user
    public function create(string $username, string $email, string $password, string $role = 'Registered'): bool
    {
        // ASVS Requirement: Secure Password Hashing
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // We use the insert() method we wrote in the core Model class last night
        return $this->insert([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'role'          => $role,
            'created_at'    => date('Y-m-d H:i:s')
        ]);
    }
}