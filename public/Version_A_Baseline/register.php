<?php
// ==============================================================================
// VERSION A: INTENTIONALLY INSECURE REGISTRATION
// This script demonstrates CWE-89 (SQL Injection), CWE-328 (Weak Hashing), 
// and CWE-352 (Cross-Site Request Forgery).
// ==============================================================================

require 'db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // INSECURE: Taking user input directly without any validation or sanitization
    $username = $_POST['username'];
    $email = $_POST['email'];
    
    // INSECURE (ASVS V2 Violation): Using ancient, easily cracked MD5 instead of Argon2id
    $password = md5($_POST['password']);

    // INSECURE (ASVS V5.5 Violation): Raw SQL string concatenation. 
    // This physically allows attackers to break out of the query and execute arbitrary commands.
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    
    try {
        // Executing the raw string directly against the database
        $pdo->exec($sql);
        $message = "<p style='color: green;'>User registered successfully! <a href='login.php'>Login here</a></p>";
    } catch (PDOException $e) {
        // INSECURE: Leaking architecture details to the DOM
        $message = "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insecure App - Register</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; background-color: #ffebee; }
        .container { background: white; padding: 20px; border-radius: 8px; width: 300px; border: 2px solid red; }
        input { width: 90%; padding: 8px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: red; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>Baseline Register</h2>
    <p><em>Warning: Intentionally Vulnerable</em></p>
    
    <?= $message ?>

    <!-- INSECURE: Form completely lacks a CSRF token -->
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Register Account</button>
    </form>
</div>

</body>
</html>