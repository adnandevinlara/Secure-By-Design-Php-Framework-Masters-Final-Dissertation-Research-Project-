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
        
        <div style="background: #e2f0d9; padding: 10px; margin-bottom: 20px;">
           <strong>Auth System Status:</strong> 
           <?= \Core\Http\Session::get('test_auth_status', 'Waiting for registration test...'); ?>
        </div>
        
        <div class="alert">
            The Context-Aware Template Engine is actively rendering this page and preventing XSS.
        </div>

        <p>Your current role is: <?php echo $this->escapeHtml($role); ?></p>
        
        <h3>Recent Security Alert:</h3>
        <code><?php echo $this->escapeHtml($maliciousInputTest); ?></code>
    </div>

    <div style="margin-top: 20px; text-align: right;">
        <a href="/logout" style="padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">Secure Logout</a>
    </div>

    <hr>
<h3>Secure Form Submission Test</h3>
<!-- This form submits to our POST route -->
<form action="/test-post" method="POST">
    
    <!-- This securely injects our generated CSRF token -->
    <?= \Core\Security\Csrf::getFormField(); ?>
    
    <button type="submit" style="padding: 10px; background: green; color: white; border: none; cursor: pointer;">
        Submit Secure Request
    </button>
</form>
</body>
</html>