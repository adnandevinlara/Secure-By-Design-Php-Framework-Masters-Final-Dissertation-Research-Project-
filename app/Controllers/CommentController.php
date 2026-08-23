<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class CommentController extends Controller
{
    // 1. Show the Comments Dashboard (With ROBUST RBAC & LEFT JOIN)
    public function index(Request $req, Response $res): void
    {
        $db = \Core\Database\Connection::getInstance();
        $userId = $_SESSION['user']['id'] ?? 0;
        
        // ROBUST ADMIN CHECK: Safely catches 'admin', 'Admin', 'super admin', etc.
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        $isAdmin = in_array($role, ['admin', 'super admin', 'super_admin']);

        // SECURE: Use LEFT JOIN so we don't hide comments if their parent post was deleted
        $query = "SELECT c.*, COALESCE(p.title, 'Unknown/Deleted Post') as post_title 
                  FROM comments c 
                  LEFT JOIN posts p ON c.post_id = p.id 
                  WHERE 1=1";
        
        $params = [];

        // If not admin, strictly lock to their own posts
        if (!$isAdmin) {
            $query .= " AND p.author_id = :user_id";
            $params[':user_id'] = $userId;
        }

        // Re-adding the missing date filters for the view
        $status = $_GET['status'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        if (!empty($status)) {
            $query .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($start_date)) {
            $query .= " AND date(c.created_at) >= :start_date";
            $params[':start_date'] = $start_date;
        }
        if (!empty($end_date)) {
            $query .= " AND date(c.created_at) <= :end_date";
            $params[':end_date'] = $end_date;
        }

        $query .= " ORDER BY c.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $comments = $stmt->fetchAll();

        // Safely ensure 'is_replied' exists so the view doesn't throw warnings
        foreach ($comments as &$c) {
            $c['is_replied'] = $c['is_replied'] ?? false;
        }

        $html = $this->view->render('comments', [
            'title' => 'Comments Moderation',
            'comments' => $comments,
            'filters' => ['status' => $status, 'start_date' => $start_date, 'end_date' => $end_date]
        ]);
        $res->html($html);
    }

    // 2. Submit a new comment
    public function store(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? null;
        $authorId = $_SESSION['user']['id'] ?? 0;
        $author = $_SESSION['user']['username'] ?? trim($_POST['author'] ?? 'Anonymous');
        $content = trim($_POST['content'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        if (empty($content) || empty($postId)) {
            $_SESSION['error'] = "Comment content is required.";
            header("Location: " . ($postId ? "/post/view?id=" . urlencode($postId) : "/"));
            exit;
        }

        if (preg_match('/(<script>|onload=|onerror=|javascript:|fetch\(|document\.cookie)/i', $content)) {
            $logLine = sprintf("[%s] [CRITICAL] [STORED_XSS_PREVENTED] [IP: %s] [Target: Comments]\n", date('c'), $ip);
            file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);
            $_SESSION['error'] = "SECURITY INTERVENTION: Stored XSS payload neutralized.";
            header("Location: /post/view?id=" . urlencode($postId));
            exit;
        }
        
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("INSERT INTO comments (post_id, author_id, author, content, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$postId, $authorId, htmlspecialchars($author, ENT_QUOTES, 'UTF-8'), htmlspecialchars($content, ENT_QUOTES, 'UTF-8')]);
        
        $_SESSION['success'] = "Comment securely processed and submitted for approval!";
        header("Location: /post/view?id=" . urlencode($postId));
        exit;
    }

    // 3. Update Status (With Robust Admin Check)
    public function updateStatus(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'pending';
        $userId = $_SESSION['user']['id'] ?? 0;
        
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        $isAdmin = in_array($role, ['admin', 'super admin', 'super_admin']);

        $db = \Core\Database\Connection::getInstance();
        
        if ($isAdmin) {
            $stmt = $db->prepare("UPDATE comments SET status = :status WHERE id = :comment_id");
            $stmt->execute([':status' => $newStatus, ':comment_id' => $commentId]);
        } else {
            $stmt = $db->prepare("UPDATE comments SET status = :status WHERE id = :comment_id AND post_id IN (SELECT id FROM posts WHERE author_id = :user_id)");
            $stmt->execute([':status' => $newStatus, ':comment_id' => $commentId, ':user_id' => $userId]);
        }

        $_SESSION['success'] = "Comment status updated!";
        header("Location: /comments");
        exit;
    }

    // 4. Delete a comment (With Robust Admin Check)
    public function delete(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        $isAdmin = in_array($role, ['admin', 'super admin', 'super_admin']);

        $db = \Core\Database\Connection::getInstance();
        
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM comments WHERE id = :comment_id");
            $stmt->execute([':comment_id' => $commentId]);
        } else {
            $stmt = $db->prepare("DELETE FROM comments WHERE id = :comment_id AND post_id IN (SELECT id FROM posts WHERE author_id = :user_id)");
            $stmt->execute([':comment_id' => $commentId, ':user_id' => $userId]);
        }

        $_SESSION['success'] = "Comment permanently deleted.";
        header("Location: /comments");
        exit;
    }
}