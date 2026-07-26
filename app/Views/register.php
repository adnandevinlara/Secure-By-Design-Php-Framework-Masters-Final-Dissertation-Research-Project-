<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Registration - PHP Framework</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 500px; margin: auto; background-color: #f4f7f6; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button { padding: 12px; background: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background: #004494; }
        .security-badge { background: #e2f0d9; color: #2e7d32; padding: 10px; text-align: center; margin-bottom: 20px; font-size: 12px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Register User</h2>
        <div class="security-badge">
            🔒 Protected by CSRF Middleware & Argon2id Hashing
        </div>

        <div style="color: red; margin-bottom: 15px; text-align: center;">
            <!-- SECURE: Context-aware escaping prevents Reflected XSS -->
            <?= htmlspecialchars(\Core\Http\Session::get('test_auth_status', '') ?? '', ENT_QUOTES, 'UTF-8'); ?>
            <?php unset($_SESSION['test_auth_status']); ?>
        </div>
        
        <form action="/register" method="POST">
            <!-- CRITICAL: Injecting the CSRF Token -->
            <?= \Core\Security\Csrf::getFormField(); ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Secure Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Create Account Securely</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="/login" style="color: #666; text-decoration: none;">Already have an account? Login here</a>
        </p>
    </div>
</body>
</html>