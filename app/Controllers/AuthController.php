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
        
        // Create a unique tracking key for this specific email address
        $throttleKey = 'login_throttle_' . md5($email);

        // 1. Check if the firewall has already locked this user out
        if (!\Core\Security\Throttler::isAllowed($throttleKey)) {
            $seconds = \Core\Security\Throttler::getRemainingLockoutSeconds($throttleKey);
            \Core\Http\Session::set('test_auth_status', "Security Alert: Too many failed attempts. Locked out for $seconds seconds.");
            header("Location: /login");
            exit;
        }

        if (empty($email) || empty($password)) {
            \Core\Http\Session::set('test_auth_status', "Security Exception: Email and password are required.");
            header("Location: /login");
            exit;
        }

        $user = \App\Models\User::findByEmail($email);

        // 2. Verify the credentials
        if ($user && password_verify($password, $user['password'])) {
            
            // Success! Clear any past failed attempts
            \Core\Security\Throttler::clear($throttleKey);
            
            session_regenerate_id(true);
            \Core\Http\Session::set('user_id', $user['id']);
            \Core\Http\Session::set('test_auth_status', "Success! Securely logged in as " . $user['username']);
            
            header("Location: /dashboard");
            exit;
        }

        // 3. If login fails, record the strike against them
        \Core\Security\Throttler::recordFailure($throttleKey);
        
        \Core\Http\Session::set('test_auth_status', "Security Exception: Invalid credentials.");
        header("Location: /login");
        exit;
    }

    // Securely logs the user out and destroys the session
    public function logout(Request $req, Response $res): void
    {
        \Core\Http\Session::destroy();
        header("Location: /login");
        exit;
    }
}