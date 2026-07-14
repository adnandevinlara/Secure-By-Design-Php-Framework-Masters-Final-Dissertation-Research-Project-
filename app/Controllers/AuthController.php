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

        // 1. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // 2. Save securely to the database
        $isCreated = \App\Models\User::create($username, $email, $hashedPassword);
        
        if ($isCreated) {
            \Core\Http\Session::set('test_auth_status', "Success! $username was securely registered and saved to the database.");
        } else {
            \Core\Http\Session::set('test_auth_status', "Error: Could not register user. Email may already be in use.");
        }
        
        // 3. Redirect back to dashboard
        header("Location: /dashboard");
        exit;
    }


    /**
     * Displays the login form
     */
    public function showLogin(Request $req, Response $res): void
    {
        $html = $this->view->render('login');
        $res->html($html);
    }

    /**
     * Processes the secure login
     */
    public function processLogin(Request $req, Response $res): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::set('test_auth_status', "Security Exception: Email and password are required.");
            header("Location: /login");
            exit;
        }

        $user = \App\Models\User::findByEmail($email);

        // Verify the user exists AND the password matches the Argon2id hash
        if ($user && password_verify($password, $user['password'])) {
            
            // ASVS Requirement: Defeat Session Fixation attacks by rotating the session ID on login
            session_regenerate_id(true);
            
            Session::set('user_id', $user['id']);
            Session::set('test_auth_status', "Success! Securely logged in as " . $user['username']);
            
            header("Location: /dashboard");
            exit;
        }

        // Generic error message to prevent username enumeration attacks
        Session::set('test_auth_status', "Security Exception: Invalid credentials.");
        header("Location: /login");
        exit;
    }
}