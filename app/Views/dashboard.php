<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure CMS Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .alert { padding: 15px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo $this->escapeHtml($username); ?>!</h1>
        
        <div class="alert">
            The Context-Aware Template Engine is actively rendering this page and preventing XSS.
        </div>

        <p>Your current role is: <?php echo $this->escapeHtml($role); ?></p>
        
        <h3>Recent Security Alert:</h3>
        <code><?php echo $this->escapeHtml($maliciousInputTest); ?></code>
    </div>
</body>
</html>