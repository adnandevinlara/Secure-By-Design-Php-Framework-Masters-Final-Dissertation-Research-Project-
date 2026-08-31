<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class CommentController extends Controller
{
    // Helper to extract ACL permissions cleanly
    private function getAcl() {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        return [
            'isAdmin' => in_array($role, ['admin', 'super admin', 'super_admin']),
            'isSubAdmin' => $role === 'sub_admin',
            'perms' => $_SESSION['user']['permissions'] ?? []
        ];
    }

    // 1. List Comments (Data Isolated!)
    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        // Data Scope: Can they see EVERYONE'S comments, or just comments on THEIR posts?
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('view_comments', $perms));

        // Point #12 & #20: JOIN with posts table to enforce data isolation
        $query = "
            SELECT c.*, p.title AS post_title, p.author_id 
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            WHERE 1=1
        ";
        
        $params = [];

        // STRICT ISOLATION: If not global admin, lock down to ONLY their own posts
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

        $query .= " ORDER BY c.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $comments = $stmt->fetchAll();

        $html = $this->view->render('comments', [
            'title' => 'Comments Moderation',
            'comments' => $comments,
            'filters' => ['status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]
        ]);
        $res->html($html);
    }

    // 2. Add New Comment (Public Frontend)
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

        $_SESSION['success'] = "Comment submitted successfully! It is awaiting moderation.";
        header("Location: /post/view?id=" . $postId);
        exit;
    }

    // 3. Update Comment Status (Point #20)
    public function updateStatus(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'approved';
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_comment', $perms));
        $db = Connection::getInstance();

        // Security Check: Verify ownership before updating
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

    // 4. Delete Comment
    public function delete(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('delete_comment', $perms));
        $db = Connection::getInstance();

        // Security Check: Verify ownership before deleting
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

    // 5. Reply to Comment (Points #11 & #13)
    public function reply(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $postId = $_POST['post_id'] ?? 0;
        $replyContent = trim($_POST['reply_content'] ?? '');
        $userId = $_SESSION['user']['id'] ?? 0;
        $authorName = $_SESSION['user']['username'] ?? 'Author';
        extract($this->getAcl());

        if (empty($replyContent)) {
            $_SESSION['error'] = "Reply cannot be empty.";
            header("Location: /comments");
            exit;
        }

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_comment', $perms));
        $db = Connection::getInstance();

        // STRICT ISOLATION: Verify they own the post they are replying to
        $stmt = $db->prepare("SELECT author_id FROM posts WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        $postOwnerId = $stmt->fetchColumn();

        if (!$hasGlobalAccess && (int)$postOwnerId !== (int)$userId) {
            $_SESSION['error'] = "Security Alert: You can only reply to comments on your own published posts.";
            header("Location: /comments");
            exit;
        }

        // Insert the reply as a pre-approved comment
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

        // Mark the original comment as replied
        $updateStmt = $db->prepare("UPDATE comments SET is_replied = 1 WHERE id = :comment_id");
        $updateStmt->execute([':comment_id' => $commentId]);

        $_SESSION['success'] = "Reply posted successfully!";
        header("Location: /comments");
        exit;
    }
}