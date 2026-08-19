<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class CommentController extends Controller
{
    // 1. Show the Comments Dashboard
    public function index(Request $req, Response $res): void
    {
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->query("SELECT * FROM comments ORDER BY created_at DESC");
        $comments = $stmt->fetchAll();

        $html = $this->view->render('comments', [
            'title' => 'Secure CMS | Comments',
            'comments' => $comments
        ]);
        $res->html($html);
    }

    // 2. Simulate an External User Comment (With XSS Trap)
    public function store(Request $req, Response $res): void
    {
        $author = trim($_POST['author'] ?? 'Anonymous');
        $content = trim($_POST['content'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        // --- LAYER 1: VALIDATION ---
        if (empty($content)) {
            $_SESSION['error'] = "Comment content cannot be empty.";
            header("Location: /comments");
            exit;
        }

        // --- LAYER 2: STORED XSS TRAP ---
        $isMalicious = false;

        // Scanning for malicious script injections meant to steal cookies or redirect users
        if (preg_match('/(<script>|onload=|onerror=|javascript:|fetch\(|document\.cookie)/i', $content)) {
            $isMalicious = true;
        }

        if ($isMalicious) {
            $logLine = sprintf("[%s] [CRITICAL] [STORED_XSS_PREVENTED] [IP: %s] [Target: Comments]\n", 
                date('c'), $ip
            );
            file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);

            $_SESSION['error'] = "SECURITY INTERVENTION: Stored XSS payload neutralized. IP logged.";
            header("Location: /comments");
            exit;
        }

        // --- SAFE EXECUTION (ASVS V5.3 Output Escaping applied before DB insertion) ---
        $safeAuthor = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("INSERT INTO comments (author, content) VALUES (?, ?)");
        $stmt->execute([$safeAuthor, $safeContent]);
        
        $_SESSION['success'] = "Comment securely processed and saved!";
        header("Location: /comments");
        exit;
    }
}