<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class BlogController extends Controller
{
    // Show all Blog Posts in the Management Table
    public function index(Request $req, Response $res): void
    {
        $db = \Core\Database\Connection::getInstance();
        
        // Use a LEFT JOIN to pull the category name alongside the post data
        $stmt = $db->query("
            SELECT posts.*, categories.name AS category_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id 
            ORDER BY posts.id DESC
        ");
        $posts = $stmt->fetchAll();

        $html = $this->view->render('posts', [
            'title' => 'Secure CMS | Blog Posts Management',
            'posts' => $posts
        ]);
        $res->html($html);
    }

    // 1. Show the Create Post Form
    public function create(Request $req, Response $res): void
    {
        // Fetch categories from the database for the dropdown
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();

        $html = $this->view->render('create_post', [
            'title' => 'Secure CMS | Create Post',
            'categories' => $categories // Pass them to the view
        ]);
        $res->html($html);
    }

    // 2. Handle the Submission (The Insecure Code Trap)
    public function store(Request $req, Response $res): void
    {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        // Grab the category ID from the form
        $categoryId = $_POST['category_id'] ?? null; 
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user = $_SESSION['user']['username'] ?? 'Guest';

        // --- LAYER 1: SERVER-SIDE VALIDATION ---
        if (empty($title) || empty($content) || empty($categoryId)) {
            $_SESSION['error'] = "All fields (including category) are required.";
            header("Location: /post/create");
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
            $logLine = sprintf("[%s] [CRITICAL] [%s] [IP: %s] [User: %s] [POST /post/store]\n", 
                date('c'), 
                $eventType, 
                $ip, 
                $user
            );
            file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);

            $_SESSION['error'] = "SECURITY INTERVENTION: Malicious payload detected. Your IP and actions have been logged.";
            header("Location: /post/create");
            exit;
        }

        // --- SAFE EXECUTION ---
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
        $db = \Core\Database\Connection::getInstance();
        
        // Update the SQL statement to include category_id
        $stmt = $db->prepare("INSERT INTO posts (title, content, author_id, category_id) VALUES (?, ?, ?, ?)");
        
        $authorId = $_SESSION['user']['id'] ?? 1; 
        
        $stmt->execute([$safeTitle, $safeContent, $authorId, $categoryId]);
        
        $_SESSION['success'] = "Post published securely!";
        header("Location: /posts"); // Redirect to the posts table, not the dashboard
        exit;
    }
}