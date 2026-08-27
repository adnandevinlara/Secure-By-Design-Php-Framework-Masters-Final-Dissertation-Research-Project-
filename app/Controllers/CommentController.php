<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class CommentController extends Controller
{
    private function getAcl() {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        return [
            'isAdmin' => in_array($role, ['admin', 'super admin', 'super_admin']),
            'isSubAdmin' => $role === 'sub_admin',
            'perms' => $_SESSION['user']['permissions'] ?? []
        ];
    }

    public function index(Request $req, Response $res): void
    {
        $db = \Core\Database\Connection::getInstance();
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        // ACL Hard-Block: Must have view rights
        if ($isSubAdmin && !in_array('view_comments', $perms)) {
            $_SESSION['error'] = "Access Denied: You do not have permission to view comments.";
            header("Location: /dashboard");
            exit;
        }

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('view_comments', $perms));

        $query = "SELECT c.*, COALESCE(p.title, 'Unknown/Deleted Post') as post_title 
                  FROM comments c 
                  LEFT JOIN posts p ON c.post_id = p.id 
                  WHERE 1=1";
        
        $params = [];

        if (!$hasGlobalAccess) {
            $query .= " AND p.author_id = :user_id";
            $params[':user_id'] = $userId;
        }

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

    // Public method - no ACL needed for frontend comment submission
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

    public function updateStatus(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'pending';
        extract($this->getAcl());

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_comments', $perms));
        
        // STRICT SECURITY: Only Admins/Sub-Admins with permission can change status
        if (!$hasGlobalAccess) {
            $_SESSION['error'] = "Security Alert: Only administrators can approve or hide comments.";
            header("Location: /comments");
            exit;
        }

        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("UPDATE comments SET status = :status WHERE id = :comment_id");
        $stmt->execute([':status' => $newStatus, ':comment_id' => $commentId]);

        $_SESSION['success'] = "Comment status updated!";
        header("Location: /comments");
        exit;
    }

    public function delete(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        extract($this->getAcl());

        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('delete_comments', $perms));
        
        // STRICT SECURITY: Only Admins/Sub-Admins with permission can delete comments
        if (!$hasGlobalAccess) {
            $_SESSION['error'] = "Security Alert: Only administrators can delete comments.";
            header("Location: /comments");
            exit;
        }

        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = :comment_id");
        $stmt->execute([':comment_id' => $commentId]);

        $_SESSION['success'] = "Comment permanently deleted.";
        header("Location: /comments");
        exit;
    }

    public function reply(Request $req, Response $res): void
    {
        $commentId = $_POST['comment_id'] ?? 0;
        $postId = $_POST['post_id'] ?? 0;
        $replyContent = trim($_POST['reply_content'] ?? '');
        
        extract($this->getAcl());

        // ACL Check: Can this user reply to comments?
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('reply_comments', $perms));
        
        $db = \Core\Database\Connection::getInstance();
        
        // If they are a standard user, ensure they actually own the post this comment is on
        if (!$hasGlobalAccess) {
            $stmt = $db->prepare("SELECT id FROM posts WHERE id = :post_id AND author_id = :user_id");
            $stmt->execute([':post_id' => $postId, ':user_id' => $_SESSION['user']['id']]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = "Security Alert: You can only reply to comments on your own posts.";
                header("Location: /comments");
                exit;
            }
        }

        if (empty($replyContent)) {
            $_SESSION['error'] = "Reply content cannot be empty.";
            header("Location: /comments");
            exit;
        }

        $authorId = $_SESSION['user']['id'] ?? 0;
        $author = $_SESSION['user']['username'] ?? 'Admin';
        
        // Format the reply nicely and auto-approve it since an admin/author is posting it
        $formattedReply = "↳ " . $replyContent; 
        
        $stmt = $db->prepare("INSERT INTO comments (post_id, author_id, author, content, status) VALUES (?, ?, ?, ?, 'approved')");
        $stmt->execute([$postId, $authorId, htmlspecialchars($author, ENT_QUOTES, 'UTF-8'), htmlspecialchars($formattedReply, ENT_QUOTES, 'UTF-8')]);

        // Attempt to update the original comment to show it was replied to
        try {
            $update = $db->prepare("UPDATE comments SET is_replied = 1 WHERE id = ?");
            $update->execute([$commentId]);
        } catch (\Exception $e) {
            // Fails safely if the is_replied column wasn't added to the DB yet
        }

        $_SESSION['success'] = "Your reply was posted successfully!";
        header("Location: /comments");
        exit;
    }
}