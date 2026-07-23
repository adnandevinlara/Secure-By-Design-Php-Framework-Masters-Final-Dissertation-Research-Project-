<?php
// ==============================================================================
// VERSION A: INTENTIONALLY INSECURE BLOG CMS
// This script demonstrates CWE-79 (Stored XSS) and CWE-89 (SQL Injection).
// Output is reflected directly to the DOM without context-aware escaping.
// ==============================================================================

require 'db.php';
session_start();

// INSECURE: Weak access control check.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize the vulnerable posts table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        author TEXT NOT NULL
    )
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // INSECURE: Taking user input directly without sanitization
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_SESSION['username'];

    // INSECURE: Raw SQL string concatenation
    $sql = "INSERT INTO posts (title, content, author) VALUES ('$title', '$content', '$author')";
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch all posts to display
$posts = [];
try {
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY id DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching posts.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insecure App - Blog</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; background-color: #e8f5e9; }
        .container { background: white; padding: 20px; border-radius: 8px; width: 600px; border: 2px solid green; margin-bottom: 20px; }
        .post { border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; }
        input, textarea { width: 90%; padding: 8px; margin: 10px 0; }
        button { padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>Baseline Blog CMS</h2>
    <a href="dashboard.php">Back to Dashboard</a> | <a href="login.php?logout=true" style="color: red;">Logout</a>
    <hr>

    <h3>Create a New Post</h3>
    <!-- INSECURE: Form lacks CSRF tokens -->
    <form method="POST" action="">
        <label>Post Title:</label><br>
        <input type="text" name="title" required><br>
        
        <label>Content:</label><br>
        <textarea name="content" rows="4" required></textarea><br>
        
        <button type="submit">Publish Post</button>
    </form>
</div>

<div class="container">
    <h3>Recent Posts</h3>
    <?php if (empty($posts)): ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <!-- INSECURE (Stored XSS): Outputting raw database content without htmlspecialchars() or escaping -->
                <h4><?= $post['title'] ?></h4>
                <p><small>Posted by: <?= $post['author'] ?></small></p>
                <p><?= $post['content'] ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>