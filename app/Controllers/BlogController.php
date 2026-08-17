<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class BlogController extends Controller
{
    // Show all Blog Posts
    public function index(Request $req, Response $res): void
    {
        // For now, we will just pass an empty array of posts. 
        // We will connect this to your database in the next step!
        $html = $this->view->render('dashboard', [
            'title' => 'Secure CMS | All Posts',
            'posts' => [],
            'stats' => ['total_users' => 0, 'total_posts' => 0, 'total_comments' => 0]
        ]);
        $res->html($html);
    }

    // 1. Show the Create Post Form
    public function create(Request $req, Response $res): void
    {
        $html = $this->view->render('create_post', [
            'title' => 'Secure CMS | Create Post'
        ]);
        $res->html($html);
    }

    // 2. Handle the Submission (The Insecure Code Trap)
    public function store(Request $req, Response $res): void
    {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user = $_SESSION['user']['username'] ?? 'Guest';

        // --- LAYER 1: SERVER-SIDE VALIDATION ---
        if (empty($title) || empty($content)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /posts/create");
            exit;
        }

        // --- LAYER 2: THE INSECURE CODE TRAP ---
        $isMalicious = false;
        $eventType = '';

        // Detect basic XSS payloads
        if (preg_match('/(<script>|onload=|onerror=|javascript:)/i', $content . $title)) {
            $isMalicious = true;
            $eventType = 'XSS_PAYLOAD_DETECTED';
        } 
        // Detect basic SQL Injection payloads
        elseif (preg_match('/(DROP TABLE|UNION SELECT|--;|OR 1=1)/i', $content . $title)) {
            $isMalicious = true;
            $eventType = 'SQLI_PAYLOAD_DETECTED';
        }

        // Trigger the Trap!
        if ($isMalicious) {
            // Write directly to the security log format you just perfected
            $logLine = sprintf("[%s] [CRITICAL] [%s] [IP: %s] [User: %s] [POST /post/store]\n", 
                date('c'), 
                $eventType, 
                $ip, 
                $user
            );
            file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);

            // Redirect back with a high-visibility warning
            $_SESSION['error'] = "SECURITY INTERVENTION: Malicious payload detected. Your IP and actions have been logged.";
            header("Location: /posts/create");
            exit;
        }

        // --- SAFE EXECUTION ---
        // If it passes the trap, safely sanitize and save to the real database
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
        // Grab the database connection
        $db = \Core\Database\Connection::getInstance();
        
        // Use a secure Prepared Statement to prevent any residual SQL injection risks
        $stmt = $db->prepare("INSERT INTO posts (title, content, author_id) VALUES (?, ?, ?)");
        
        // Fallback to User ID 1 if the session doesn't have an ID yet
        $authorId = $_SESSION['user']['id'] ?? 1; 
        
        $stmt->execute([$safeTitle, $safeContent, $authorId]);
        
        $_SESSION['success'] = "Post published securely!";
        header("Location: /dashboard");
        exit;
    }
}