<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class BlogController extends Controller
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

    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl()); // Loads $isAdmin, $isSubAdmin, and $perms

        // 1. ACL Hard-Block: Stop Sub-Admins who don't have view rights
        if ($isSubAdmin && !in_array('view_blogs', $perms)) {
            $_SESSION['error'] = "Access Denied: You do not have permission to view blogs.";
            header("Location: /dashboard");
            exit;
        }

        $categories = [];
        try {
            $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
        } catch (\PDOException $e) {}

        // 2. ACL Data Scope: Can they see EVERYONE'S posts, or just their own?
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('view_blogs', $perms));

        $query = "
            SELECT p.*, c.name AS category_name 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1
        ";
        
        $params = [];

        if (!$hasGlobalAccess) {
            $query .= " AND p.author_id = :user_id";
            $params[':user_id'] = $userId;
        }

        // Apply Search Filters
        $title = $_GET['title'] ?? '';
        $category_id = $_GET['category_id'] ?? '';
        $status = $_GET['status'] ?? '';
        
        if (!empty($title)) {
            $query .= " AND p.title LIKE :title";
            $params[':title'] = "%$title%";
        }
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        if (!empty($status)) {
            $query .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        $html = $this->view->render('posts', [
            'title' => 'Blog Posts Management',
            'posts' => $posts,
            'categories' => $categories,
            'filters' => [
                'title' => $title,
                'category_id' => $category_id,
                'status' => $status
            ]
        ]);
        $res->html($html);
    }

    public function updateStatus(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'published';
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        if ($isSubAdmin && !in_array('status_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Change Status' permission.";
            header("Location: /posts");
            exit;
        }

        $db = Connection::getInstance();
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('status_blog', $perms));
        
        if ($hasGlobalAccess) {
            $stmt = $db->prepare("UPDATE posts SET status = :status WHERE id = :post_id");
            $stmt->execute([':status' => $newStatus, ':post_id' => $postId]);
        } else {
            $stmt = $db->prepare("UPDATE posts SET status = :status WHERE id = :post_id AND author_id = :user_id");
            $stmt->execute([':status' => $newStatus, ':post_id' => $postId, ':user_id' => $userId]);
        }

        $_SESSION['success'] = "Post status updated to " . ucfirst($newStatus) . "!";
        header("Location: /posts");
        exit;
    }

    public function delete(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        extract($this->getAcl());

        if ($isSubAdmin && !in_array('delete_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Delete Blog' permission.";
            header("Location: /posts");
            exit;
        }

        $db = Connection::getInstance();
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('delete_blog', $perms));
        
        if ($hasGlobalAccess) {
            $stmt = $db->prepare("DELETE FROM posts WHERE id = :post_id");
            $stmt->execute([':post_id' => $postId]);
        } else {
            $stmt = $db->prepare("DELETE FROM posts WHERE id = :post_id AND author_id = :user_id");
            $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        }

        $_SESSION['success'] = "Post permanently deleted.";
        header("Location: /posts");
        exit;
    }

    public function create(Request $req, Response $res): void 
    {
        extract($this->getAcl());
        
        if ($isSubAdmin && !in_array('create_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Create Blog' permission.";
            header("Location: /posts");
            exit;
        }

        $db = Connection::getInstance();
        $categories = [];
        try {
            $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
        } catch (\PDOException $e) {}

        $html = $this->view->render('create_post', [
            'title' => 'Create New Post',
            'categories' => $categories
        ]);
        $res->html($html);
    }

    public function store(Request $req, Response $res): void 
    {
        extract($this->getAcl());
        
        if ($isSubAdmin && !in_array('create_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Create Blog' permission.";
            header("Location: /posts");
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $status = $_POST['status'] ?? 'published';
        $content = trim($_POST['content'] ?? '');
        $authorId = $_SESSION['user']['id'] ?? 0;

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = "Title and Content are required fields.";
            header("Location: /post/create");
            exit;
        }

        // 🛡️ Active Threat Interception (The XSS Trap)
        if (stripos($content, '<script>') !== false || stripos($title, '<script>') !== false) {
            // Log to database for the Admin Dashboard Security Events feed
            $db = \Core\Database\Connection::getInstance();
            $stmt = $db->prepare("INSERT INTO security_logs (event_type, ip_address, user, details, severity, request_url, description, timestamp) VALUES (:event_type, :ip, :user, :details, :severity, :request_url, :description, :timestamp)");
            $stmt->execute([
                ':event_type' => 'XSS_PAYLOAD_DETECTED',
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                ':user' => $_SESSION['user']['username'] ?? 'Guest',
                ':details' => 'Blocked malicious script payload on Post submission',
                ':severity' => 'CRITICAL',
                ':request_url' => $_SERVER['REQUEST_URI'] ?? '/post/store',
                ':description' => 'Blocked malicious script payload on Post submission',
                ':timestamp' => date('Y-m-d H:i:s') // <--- Manually passing the exact time!
            ]);

            $_SESSION['error'] = "Security Alert: Malicious XSS payload intercepted and neutralized. This incident has been logged.";
            header("Location: /post/create");
            exit;
        }

        // 🛡️ Secure Image Upload Handling (Point #16)
        $bannerPath = null;
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/banners/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileTmpPath = $_FILES['banner_image']['tmp_name'];
            $fileSize = $_FILES['banner_image']['size'];
            
            // Limit to 2MB
            if ($fileSize > 2097152) {
                $_SESSION['error'] = "Upload failed: Image exceeds the 2MB size limit.";
                header("Location: /post/create");
                exit;
            }

            // Secure MIME Type validation (Ignore spoofed extensions)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileTmpPath);
            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                $_SESSION['error'] = "Upload failed: Invalid file type. Only JPG, PNG, and WEBP are allowed.";
                header("Location: /post/create");
                exit;
            }

            // Generate Cryptographic filename
            $fileExtension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $newFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
            
            if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                $bannerPath = '/uploads/banners/' . $newFileName;
            }
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare("
            INSERT INTO posts (title, category_id, content, author_id, status, banner_image) 
            VALUES (:title, :category_id, :content, :author_id, :status, :banner_image)
        ");
        
        $stmt->execute([
            ':title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            ':category_id' => $categoryId,
            ':content' => $content, // Note Editor HTML
            ':author_id' => $authorId,
            ':status' => $status,
            ':banner_image' => $bannerPath
        ]);

        $_SESSION['success'] = "New post successfully published!";
        header("Location: /posts");
        exit;
    }

    public function edit(Request $req, Response $res): void 
    {
        extract($this->getAcl());
        
        if ($isSubAdmin && !in_array('edit_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Edit Blog' permission.";
            header("Location: /posts");
            exit;
        }

        $db = Connection::getInstance();
        $postId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('edit_blog', $perms));

        if ($hasGlobalAccess) {
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
            $stmt->execute([':id' => $postId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id AND author_id = :user_id");
            $stmt->execute([':id' => $postId, ':user_id' => $userId]);
        }
        
        $post = $stmt->fetch();

        if (!$post) {
            $_SESSION['error'] = "Post not found or permission denied.";
            header("Location: /posts");
            exit;
        }

        $categories = [];
        try {
            $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
        } catch (\PDOException $e) {}

        $html = $this->view->render('edit_post', [
            'title' => 'Edit Post',
            'post' => $post,
            'categories' => $categories
        ]);
        $res->html($html);
    }

    public function update(Request $req, Response $res): void 
    {
        extract($this->getAcl());
        
        if ($isSubAdmin && !in_array('edit_blog', $perms)) {
            $_SESSION['error'] = "Access Denied: Missing 'Edit Blog' permission.";
            header("Location: /posts");
            exit;
        }

        $postId = $_POST['post_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $status = $_POST['status'] ?? 'published';
        $content = trim($_POST['content'] ?? '');
        $userId = $_SESSION['user']['id'] ?? 0;
        
        // 🛡️ Active Threat Interception
        if (stripos($content, '<script>') !== false || stripos($title, '<script>') !== false) {
            // Log to database for the Admin Dashboard Security Events feed
            $db = \Core\Database\Connection::getInstance();
            $stmt = $db->prepare("INSERT INTO security_logs (event_type, ip_address, user, details, severity, request_url, description, timestamp) VALUES (:event_type, :ip, :user, :details, :severity, :request_url, :description, :timestamp)");
            $stmt->execute([
                ':event_type' => 'XSS_PAYLOAD_DETECTED',
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                ':user' => $_SESSION['user']['username'] ?? 'Guest',
                ':details' => 'Blocked malicious script payload on Post update',
                ':severity' => 'CRITICAL',
                ':request_url' => $_SERVER['REQUEST_URI'] ?? '/post/update',
                ':description' => 'Blocked malicious script payload on Post update',
                ':timestamp' => date('Y-m-d H:i:s') // <--- Manually passing the exact time!
            ]);

            $_SESSION['error'] = "Security Alert: Malicious XSS payload intercepted and neutralized. This incident has been logged.";
            header("Location: /post/edit?id=" . $postId);
            exit;
        }

        $db = Connection::getInstance();
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('edit_blog', $perms));

        // 🛡️ Secure Image Upload Handling (For Edits)
        $bannerPath = null;
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/banners/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileTmpPath = $_FILES['banner_image']['tmp_name'];
            if ($_FILES['banner_image']['size'] <= 2097152) { // 2MB limit
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                if (in_array($finfo->file($fileTmpPath), ['image/jpeg', 'image/png', 'image/webp'])) {
                    $ext = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
                    $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $bannerPath = '/uploads/banners/' . $newFileName;
                    }
                }
            }
        }

        // Build dynamic SQL based on whether a new image was uploaded
        $sql = "UPDATE posts SET title = :title, category_id = :category_id, content = :content, status = :status";
        $params = [
            ':title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), 
            ':category_id' => $categoryId, 
            ':content' => $content, 
            ':status' => $status, 
            ':post_id' => $postId
        ];

        if ($bannerPath) {
            $sql .= ", banner_image = :banner_image";
            $params[':banner_image'] = $bannerPath;
        }

        if (!$hasGlobalAccess) {
            $sql .= " WHERE id = :post_id AND author_id = :user_id";
            $params[':user_id'] = $userId;
        } else {
            $sql .= " WHERE id = :post_id";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Post successfully updated!";
        header("Location: /posts");
        exit;
    }
}