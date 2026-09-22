<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class CommentController extends Controller
{
    private function getAcl() {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        return [
            'isAdmin' => in_array($role, ['admin', 'super admin', 'super_admin', 'administrator']),
            'isSubAdmin' => $role === 'sub_admin',
            'isUser' => $role === 'user',
            'perms' => $_SESSION['user']['permissions'] ?? [],
            'userId' => (int)($_SESSION['user']['id'] ?? 0)
        ];
    }

    // Strict Centralized RBAC (Updated for Read-Only Default Access)
    private function checkAccess($requiredAction = 'view_comments') {
        extract($this->getAcl());
        
        if ($userId === 0) {
            $_SESSION['error'] = "Session expired.";
            header("Location: /login");
            exit;
        }

        if ($isAdmin) return true;
        if ($isUser) return true; // Handled strictly by SQL ownership checks in the methods

        if ($isSubAdmin) {
            // 1. ALWAYS allow them to open the page (Read-Only access by default)
            if ($requiredAction === 'view_comments') {
                return true;
            }
            
            // 2. If they try to click Edit, Delete, or Reply without permission, block them!
            if (!in_array($requiredAction, $perms)) {
                $_SESSION['error'] = "Access Denied: You do not have permission to moderate, edit, or reply to comments.";
                // Send them right back to the comments page to see the error
                header("Location: /comments"); 
                exit;
            }
            return true;
        }
        
        $_SESSION['error'] = "Access Denied.";
        header("Location: /dashboard");
        exit;
    }

    public function index(Request $req, Response $res): void
    {
        $this->checkAccess('view_comments');
        $db = Connection::getInstance();
        extract($this->getAcl());

        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $role = $_GET['role'] ?? ''; 

        // If they lack 'view_comments', this becomes false, and they only see their OWN comments
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('view_comments', $perms));

        $query = "
            SELECT c.*, p.title AS post_title, p.author_id, u.role AS commenter_role
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            LEFT JOIN users u ON c.author_id = u.id
            WHERE 1=1
        ";
        
        $params = [];

        // STRICT ISOLATION
        if (!$hasGlobalAccess) {
            $query .= " AND p.author_id = :user_id";
            $params[':user_id'] = $userId;
        }

        if (!empty($status)) {
            $query .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($startDate)) {
            $query .= " AND date(c.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $query .= " AND date(c.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if (!empty($role)) {
            $query .= " AND u.role = :role";
            $params[':role'] = $role;
        }

        $query .= " ORDER BY c.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $comments = $stmt->fetchAll();

        $html = $this->view->render('comments', [
            'title' => 'Comments Moderation',
            'comments' => $comments,
            'filters' => [
                'status' => $status, 
                'start_date' => $startDate, 
                'end_date' => $endDate,
                'role' => $role
            ]
        ]);
        $res->html($html);
    }

    // Add New Comment (Public Frontend - Anyone logged in can do this)
    public function store(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? 0;
        $content = trim($_POST['content'] ?? '');
        $authorId = $_SESSION['user']['id'] ?? 0;
        $author = $_SESSION['user']['username'] ?? 'Anonymous';

        if (!$authorId) {
            $_SESSION['error'] = "You must be logged in to comment.";
            header("Location: /post/view?id=" . $postId);
            exit;
        }

        if (empty($content)) {
            $_SESSION['error'] = "Comment cannot be empty.";
            header("Location: /post/view?id=" . $postId);
            exit;
        }

        // Active XSS Trap
        if (stripos($content, '<script>') !== false) {
            $_SESSION['error'] = "Security Alert: Malicious payload intercepted.";
            header("Location: /post/view?id=" . $postId);
            exit;
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare("
            INSERT INTO comments (post_id, author_id, author, content, status) 
            VALUES (:post_id, :author_id, :author, :content, 'pending')
        ");
        
        $stmt->execute([
            ':post_id' => $postId,
            ':author_id' => $authorId,
            ':author' => htmlspecialchars($author, ENT_QUOTES, 'UTF-8'),
            ':content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
        ]);

        $_SESSION['comment_success'] = "Comment is submitted for admin approval.";
        header("Location: /post/view?id=" . $postId);
        exit;
    }

    public function updateStatus(Request $req, Response $res): void
    {
        $this->checkAccess('status_comment');
        extract($this->getAcl());

        $commentId = $_POST['comment_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'approved';
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_comment', $perms));
        $db = Connection::getInstance();

        $stmt = $db->prepare("SELECT p.author_id FROM comments c JOIN posts p ON c.post_id = p.id WHERE c.id = :id");
        $stmt->execute([':id' => $commentId]);
        $ownerId = $stmt->fetchColumn();

        if (!$hasGlobalAccess && (int)$ownerId !== (int)$userId) {
            $_SESSION['error'] = "Security Alert: You can only moderate comments on your own posts.";
            header("Location: /comments");
            exit;
        }

        $updateStmt = $db->prepare("UPDATE comments SET status = :status WHERE id = :id");
        $updateStmt->execute([':status' => $newStatus, ':id' => $commentId]);

        $_SESSION['success'] = "Comment status updated to " . ucfirst($newStatus) . "!";
        header("Location: /comments");
        exit;
    }

    public function delete(Request $req, Response $res): void
    {
        $this->checkAccess('delete_comment');
        extract($this->getAcl());
        
        $commentId = $_POST['comment_id'] ?? 0;
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('delete_comment', $perms));
        $db = Connection::getInstance();

        $stmt = $db->prepare("SELECT p.author_id FROM comments c JOIN posts p ON c.post_id = p.id WHERE c.id = :id");
        $stmt->execute([':id' => $commentId]);
        $ownerId = $stmt->fetchColumn();

        if (!$hasGlobalAccess && (int)$ownerId !== (int)$userId) {
            $_SESSION['error'] = "Security Alert: You can only delete comments on your own posts.";
            header("Location: /comments");
            exit;
        }

        $deleteStmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        $deleteStmt->execute([':id' => $commentId]);

        $_SESSION['success'] = "Comment permanently deleted.";
        header("Location: /comments");
        exit;
    }

    public function reply(Request $req, Response $res): void
    {
        $this->checkAccess('reply_comment');
        extract($this->getAcl());

        $commentId = $_POST['comment_id'] ?? 0;
        $postId = $_POST['post_id'] ?? 0;
        $replyContent = trim($_POST['reply_content'] ?? '');
        $authorName = $_SESSION['user']['username'] ?? 'Author';

        if (empty($replyContent)) {
            $_SESSION['error'] = "Reply cannot be empty.";
            header("Location: /comments");
            exit;
        }

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_comment', $perms));
        $db = Connection::getInstance();

        $stmt = $db->prepare("SELECT author_id FROM posts WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        $postOwnerId = $stmt->fetchColumn();

        if (!$hasGlobalAccess && (int)$postOwnerId !== (int)$userId) {
            $_SESSION['error'] = "Security Alert: You can only reply to comments on your own published posts.";
            header("Location: /comments");
            exit;
        }

        $insertStmt = $db->prepare("
            INSERT INTO comments (post_id, author_id, author, content, status) 
            VALUES (:post_id, :author_id, :author, :content, 'approved')
        ");
        $insertStmt->execute([
            ':post_id' => $postId,
            ':author_id' => $userId,
            ':author' => htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') . ' (Author)',
            ':content' => htmlspecialchars($replyContent, ENT_QUOTES, 'UTF-8')
        ]);

        $updateStmt = $db->prepare("UPDATE comments SET is_replied = 1 WHERE id = :comment_id");
        $updateStmt->execute([':comment_id' => $commentId]);

        $_SESSION['success'] = "Reply posted successfully!";
        header("Location: /comments");
        exit;
    }
}