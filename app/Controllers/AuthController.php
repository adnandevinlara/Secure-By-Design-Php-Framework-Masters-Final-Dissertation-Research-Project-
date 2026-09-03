<?php

namespace App\Controllers;

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Core\Http\Session;

class AuthController extends Controller
{
    /**
     * Displays the registration form
     */
    public function showRegister(Request $req, Response $res): void
    {
        // 🛡️ SECURITY BLOCK: If already logged in, redirect to Dashboard
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }

        $html = $this->view->render('register');
        $res->html($html);
    }

    /**
     * Processes the registration securely
     */
    public function processRegister(Request $req, Response $res): void
    {
        // 1. Sanitize the incoming data
        $username = \Core\Security\Validator::sanitizeString($_POST['username'] ?? '');
        $email = \Core\Security\Validator::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; 
        $passwordConfirm = $_POST['password_confirm'] ?? ''; 

        // 2. Validate required fields & matching passwords
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Security Exception: All fields are required.";
            header("Location: /register");
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Security Exception: Passwords do not match.";
            header("Location: /register");
            exit;
        }

        // 3. Validate email format
        if (!\Core\Security\Validator::isValidEmail($email)) {
            $_SESSION['error'] = "Security Exception: Invalid email format.";
            header("Location: /register");
            exit;
        }

        // 4. Validate password strength
        if (!\Core\Security\Validator::isStrongPassword($password)) {
            $_SESSION['error'] = "Security Exception: Password must be at least 8 characters long and contain a number, an uppercase, and a lowercase letter.";
            header("Location: /register");
            exit;
        }

        // 5. Hash the password and save to database
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $isCreated = \App\Models\User::create($username, $email, $hashedPassword);
        
        if ($isCreated) {
            $_SESSION['success'] = "Success! $username was securely registered. Please log in.";
            header("Location: /login");
        } else {
            $_SESSION['error'] = "Error: Could not register user. Email may already be in use.";
            header("Location: /register");
        }
        exit;
    }


    /**
     * Displays the login form
     */
    public function showLogin(Request $req, Response $res): void
    {
        // 🛡️ SECURITY BLOCK: If already logged in, redirect to Dashboard
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }

        $html = $this->view->render('login');
        $res->html($html);
    }

    // Processes the secure login
    public function processLogin(Request $req, Response $res): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Create a unique tracking key for this specific email address
        $throttleKey = 'login_throttle_' . md5($email);

        // 1. Check if the firewall has already locked this user out
        if (!\Core\Security\Throttler::isAllowed($throttleKey)) {
            $seconds = \Core\Security\Throttler::getRemainingLockoutSeconds($throttleKey);
            
            // ADDED: Log the active lockout block
            \Core\Security\Logger::log('WARNING', 'BRUTE_FORCE_BLOCKED', "Active lockout enforced for email attempt: $email. Remaining: $seconds seconds.");
            
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
            
            \Core\Security\Throttler::clear($throttleKey);
            session_regenerate_id(true); 
            
            // 1. Set the raw session keys required by the AuthMiddleware
            \Core\Http\Session::set('user_id', $user['id']);
            \Core\Http\Session::set('role', $user['role']);
            
            // 2. Set the grouped array required by the Profile & Dashboard UI (Now with ACL Permissions)
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                // Decode the JSON permissions back into a PHP array (defaults to empty array if none)
                'permissions' => json_decode($user['permissions'] ?? '[]', true) ?? []
            ];
            
            \Core\Http\Session::set('test_auth_status', "Success! Securely logged in as " . $user['username']);
            header("Location: /dashboard");
            exit;
        }

        // 3. Failed Login - safely increment the throttler
        if (method_exists('\Core\Security\Throttler', 'hit')) {
            \Core\Security\Throttler::hit($throttleKey);
        } elseif (method_exists('\Core\Security\Throttler', 'addAttempt')) {
            \Core\Security\Throttler::addAttempt($throttleKey);
        } elseif (method_exists('\Core\Security\Throttler', 'increment')) {
            \Core\Security\Throttler::increment($throttleKey);
        }
        
        \Core\Security\Logger::log('WARNING', 'FAILED_LOGIN', "Failed login attempt for email: $email");
        \Core\Http\Session::set('test_auth_status', "Security Exception: Invalid credentials.");
        header("Location: /login");
        exit;
    }

    // Securely logs the user out and destroys the session
    public function logout(Request $req, Response $res): void
    {
        // 1. Empty the session array from memory
        $_SESSION = [];

        // 2. Destroy the session cookie in the user's browser (ASVS V3 Compliant)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // 3. Destroy the session file on the server
        session_destroy();

        // 4. Redirect safely to the login page
        header("Location: /login");
        exit;
    }

    // Show the Forgot Password form
    public function showForgotPassword(Request $req, Response $res): void
    {
        // 🛡️ SECURITY BLOCK: If already logged in, redirect to Dashboard
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }

        $html = $this->view->render('forgot_password', [
            'title' => 'Secure CMS | Forgot Password'
        ]);
        $res->html($html);
    }

    // Show the Reset Password (OTP) form
    public function showResetPassword(Request $req, Response $res): void
    {
        // 🛡️ SECURITY BLOCK: If already logged in, redirect to Dashboard
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }

        $html = $this->view->render('reset_password', [
            'title' => 'Secure CMS | Create New Password'
        ]);
        $res->html($html);
    }

    // Process the Forgot Password request and send Google SMTP Email
    public function processForgotPassword(Request $req, Response $res): void
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['error'] = "Please enter your email address.";
            header("Location: /forgot-password");
            exit;
        }

        $db = Connection::getInstance();

        // 1. Verify the user actually exists
        $stmt = $db->prepare("SELECT id, username FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Security Best Practice: Don't reveal if the email exists or not to prevent user enumeration
            $_SESSION['success'] = "If that email exists in our system, an OTP has been sent.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;
        }

        // 2. Generate a secure 6-digit OTP and expiration time (15 minutes from now)
        $otp = sprintf("%06d", random_int(100000, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 3. Save the OTP to the database
        $stmt = $db->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (:email, :otp, :expires_at)");
        $stmt->execute([
            ':email' => $email,
            ':otp' => password_hash($otp, PASSWORD_ARGON2ID), // Securely hash the OTP just like a password
            ':expires_at' => $expiresAt
        ]);

        // 4. Send the Email using Google SMTP & PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // PUT YOUR REAL EMAIL HERE
            $mail->Username   = 'Mail.imdadullah@gmail.com'; 
            
            // PUT YOUR 16-LETTER APP PASSWORD HERE
            $mail->Password   = '';     
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('Mail.imdadullah@gmail.com', 'Secure CMS Admin');
            $mail->addAddress($email, $user['username']);

            // Content
            $mail->isHTML(true);
            
            // THIS IS THE TITLE OF THE EMAIL
            $mail->Subject = 'Your Password Reset OTP - Secure CMS'; 
            
            $mail->Body    = "
                <h3>Hello {$user['username']},</h3>
                <p>We received a request to reset your password.</p>
                <p>Your 6-digit OTP is: <b style='font-size: 24px; color: #0d6efd;'>{$otp}</b></p>
                <p>This code will expire in 15 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();
            
            $_SESSION['success'] = "An OTP has been sent to your email address.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;

        } catch (Exception $e) {
            // Log the error securely and show a generic error to the user
            error_log("Mail Error: {$mail->ErrorInfo}");
            $_SESSION['error'] = "There was a problem sending the email. Please try again later.";
            header("Location: /forgot-password");
            exit;
        }
    }

    // Verify OTP and Save New Password
    public function processResetPassword(Request $req, Response $res): void
    {
        $email = $_POST['email'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // 1. Basic Validation
        if (empty($email) || empty($otp) || empty($password)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;
        }

        $db = Connection::getInstance();

        // 2. Fetch the most recent OTP record for this email
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = :email ORDER BY id DESC LIMIT 1");
        $stmt->execute([':email' => $email]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            $_SESSION['error'] = "Invalid request. Please try resetting your password again.";
            header("Location: /forgot-password");
            exit;
        }

        // 3. Check if the OTP has expired
        if (strtotime($resetRecord['expires_at']) < time()) {
            $_SESSION['error'] = "This OTP has expired. Please request a new one.";
            header("Location: /forgot-password");
            exit;
        }

        // 4. Securely verify the OTP (since we hashed it earlier!)
        if (!password_verify($otp, $resetRecord['otp'])) {
            $_SESSION['error'] = "The OTP entered is incorrect.";
            header("Location: /reset-password?email=" . urlencode($email));
            exit;
        }

        // 5. Update the user's password in the database
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':email' => $email
        ]);

        // 6. Clean up: Delete the used OTP record so it can't be reused
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute([':email' => $email]);

        // 7. Redirect to login with success message
        $_SESSION['success'] = "Password successfully updated! You can now log in securely.";
        header("Location: /login");
        exit;
        }

        public function showChangePassword(Request $req, Response $res): void
    {
        $html = $this->view->render('change_password', ['title' => 'Change Password']);
        $res->html($html);
    }

    public function processChangePassword(Request $req, Response $res): void
    {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 0;

        if (empty($current) || empty($new) || empty($confirm)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /change-password");
            exit;
        }

        if ($new !== $confirm) {
            $_SESSION['error'] = "New passwords do not match.";
            header("Location: /change-password");
            exit;
        }

        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        // Security check: Must prove they know the current password
        if (!$user || !password_verify($current, $user['password'])) {
            $_SESSION['error'] = "Your current password is incorrect.";
            header("Location: /change-password");
            exit;
        }

        // Hash and save the new password
        $hashed = password_hash($new, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $hashed, ':id' => $userId]);

        $_SESSION['success'] = "Password successfully updated!";
        header("Location: /dashboard");
        exit;
    }
}