<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Session;

class AuthController extends Controller
{
    /**
     * Displays the registration form
     */
    public function showRegister(Request $req, Response $res): void
    {
        $html = $this->view->render('register');
        $res->html($html);
    }

    /**
     * Processes the registration securely
     */
    public function processRegister(Request $req, Response $res): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $res->html("Security Exception: All fields are required.");
            return;
        }

        // OWASP ASVS Requirement: Strong Cryptographic Hashing
        // Argon2id mathematically protects against GPU brute-force attacks
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // Here we will eventually insert the user into the database using our ORM
        // For right now, we will securely store a success state in the session
        
        Session::set('test_auth_status', "User $username securely registered with Argon2id hash: " . substr($hashedPassword, 0, 15) . "...");
        
        // Redirect back to dashboard to see the result
        header("Location: /dashboard");
        exit;
    }
}