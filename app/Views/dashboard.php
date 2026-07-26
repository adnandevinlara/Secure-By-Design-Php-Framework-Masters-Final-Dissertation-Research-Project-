<!-- views/dashboard.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure CMS Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .alert { padding: 15px; background-color: #fff3cd; color: #856404; border-radius: 4px; margin-bottom: 20px; }
        .post { border-bottom: 1px solid #eee; padding: 15px 0; }
        .post:last-child { border-bottom: none; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .security-badge { background: #e2f0d9; color: #2e7d32; padding: 10px; text-align: center; margin-bottom: 20px; font-size: 12px; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="container">
        <!-- Dynamically pulling the username if passed, or defaulting safely -->
        <h1>Welcome, <?= htmlspecialchars($username ?? 'Secure User', ENT_QUOTES, 'UTF-8'); ?>!</h1>
        
        <div class="security-badge">
            🛡️ Protected by CSRF Middleware, Custom ORM, & Context-Aware Escaping
        </div>

        <?php if ($status = \Core\Http\Session::get('test_auth_status')): ?>
            <div class="alert">
                <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['test_auth_status']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/logout" style="text-align: right;">
            <?= \Core\Security\Csrf::getFormField(); ?>
            <button type="submit" style="background: #dc3545; padding: 8px 15px;">Secure Logout</button>
        </form>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Create a New Post</h3>
        <form method="POST" action="/post/store">
            <!-- CRITICAL: Injecting the CSRF Token for form submissions -->
            <?= \Core\Security\Csrf::getFormField(); ?>
            
            <label>Post Title:</label>
            <input type="text" name="title" required>
            
            <label>Content:</label>
            <textarea name="content" rows="4" required></textarea>
            
            <button type="submit">Publish Securely</button>
        </form>
    </div>

    <div class="container">
        <h3>Community Posts</h3>
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <!-- SECURE: Neutralizing Stored XSS Payloads on output -->
                    <h4><?= htmlspecialchars($post['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></h4>
                    <p><?= htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666;">No posts yet. Be the first to securely publish!</p>
        <?php endif; ?>
    </div>

</body>
</html>