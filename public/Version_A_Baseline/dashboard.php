<?php
session_start();

// INSECURE: Weak access control check. 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// INSECURE: No CSRF protection on logout or state changes.
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Insecure App - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; background-color: #e8f5e9; }
        .container { background: white; padding: 20px; border-radius: 8px; width: 400px; border: 2px solid green; }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome to the Baseline Dashboard</h2>
    <!-- INSECURE (XSS): Echoing session data without context-aware escaping -->
    <p>Hello, <strong><?= $_SESSION['username'] ?></strong>!</p>
    
    <p>This is the protected area of the insecure application.</p>
    
    <a href="?logout=true" style="color: red;">Logout</a>

    <br><br>
    <a href="blog.php" style="color: blue; text-decoration: underline;">Go to the Blog CMS</a>
</div>

</body>
</html>