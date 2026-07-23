<?php
// ==============================================================================
// VERSION A: INTENTIONALLY INSECURE LOGIN
// This script demonstrates Authentication Bypass via SQL Injection, 
// CWE-307 (Improper Restriction of Excessive Authentication Attempts),
// and CWE-79 (Cross-Site Scripting).
// ==============================================================================

require 'db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // INSECURE: Hashing the incoming password with weak MD5 to match the database
    $password = md5($_POST['password']);

    // INSECURE (ASVS V5.5 Violation): Raw SQL authentication query.
    // An attacker can log in as ANY user without knowing their password by manipulating the $email string.
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    
    try {
        $stmt = $pdo->query($sql);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // INSECURE: Vulnerable session management. No session regeneration to prevent fixation.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirecting to an insecure dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // INSECURE (ASVS V5.3 Violation): Reflecting user input directly back to the screen without escaping.
            // If an attacker puts a malicious JavaScript payload in the email field, it will execute here (Reflected XSS).
            $message = "<p style='color: red;'>Invalid credentials for email: $email</p>";
        }
    } catch (PDOException $e) {
        $message = "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insecure App - Login</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; background-color: #ffebee; }
        .container { background: white; padding: 20px; border-radius: 8px; width: 300px; border: 2px solid red; }
        input { width: 90%; padding: 8px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: red; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>Baseline Login</h2>
    <p><em>Warning: Intentionally Vulnerable</em></p>
    
    <?= $message ?>

    <!-- INSECURE: Form lacks CSRF tokens and rate limiting -->
    <form method="POST" action="">
        <label>Email:</label>
        <input type="text" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Login</button>
    </form>
    
    <p><a href="register.php">Create an Account</a></p>
</div>

</body>
</html>