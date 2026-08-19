<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class CategoryController extends Controller
{
    // 1. Show the Categories Page
    public function index(Request $req, Response $res): void
    {
        $db = \Core\Database\Connection::getInstance();
        
        // Let's assume your categories table has id, name, and created_at columns
        // Adjust if your database schema uses different column names!
        $stmt = $db->query("SELECT * FROM categories ORDER BY created_at DESC");
        $categories = $stmt->fetchAll();

        $html = $this->view->render('categories', [
            'title' => 'Secure CMS | Categories',
            'categories' => $categories
        ]);
        $res->html($html);
    }

    // 2. Handle New Category Submission (With the Trap)
    public function store(Request $req, Response $res): void
    {
        $name = trim($_POST['name'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user = $_SESSION['user']['username'] ?? 'Guest';

        // --- LAYER 1: SERVER-SIDE VALIDATION ---
        if (empty($name)) {
            $_SESSION['error'] = "Category name is required.";
            header("Location: /categories");
            exit;
        }

        // --- LAYER 2: THE INSECURE CODE TRAP ---
        $isMalicious = false;
        $eventType = '';

        if (preg_match('/(<script>|onload=|onerror=|javascript:)/i', $name)) {
            $isMalicious = true;
            $eventType = 'XSS_PAYLOAD_DETECTED';
        } elseif (preg_match('/(DROP TABLE|UNION SELECT|--;|OR 1=1)/i', $name)) {
            $isMalicious = true;
            $eventType = 'SQLI_PAYLOAD_DETECTED';
        }

        if ($isMalicious) {
            $logLine = sprintf("[%s] [CRITICAL] [%s] [IP: %s] [User: %s] [POST /categories/store]\n", 
                date('c'), $eventType, $ip, $user
            );
            file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);

            $_SESSION['error'] = "SECURITY INTERVENTION: Malicious payload detected. Action logged.";
            header("Location: /categories");
            exit;
        }

        // --- SAFE EXECUTION ---
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$safeName]);
        
        $_SESSION['success'] = "Category created securely!";
        header("Location: /categories");
        exit;
    }
}