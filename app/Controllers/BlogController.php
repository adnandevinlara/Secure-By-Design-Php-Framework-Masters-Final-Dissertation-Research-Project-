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

        $db = Connection::getInstance();
        $stmt = $db->prepare("
            INSERT INTO posts (title, category_id, content, author_id, status) 
            VALUES (:title, :category_id, :content, :author_id, :status)
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':category_id' => $categoryId,
            ':content' => $content,
            ':author_id' => $authorId,
            ':status' => $status
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
        
        $db = Connection::getInstance();
        $hasGlobalAccess = $isAdmin || ($isSubAdmin && in_array('edit_blog', $perms));

        if ($hasGlobalAccess) {
            $stmt = $db->prepare("UPDATE posts SET title = :title, category_id = :category_id, content = :content, status = :status WHERE id = :post_id");
            $stmt->execute([':title' => $title, ':category_id' => $categoryId, ':content' => $content, ':status' => $status, ':post_id' => $postId]);
        } else {
            $stmt = $db->prepare("UPDATE posts SET title = :title, category_id = :category_id, content = :content, status = :status WHERE id = :post_id AND author_id = :user_id");
            $stmt->execute([':title' => $title, ':category_id' => $categoryId, ':content' => $content, ':status' => $status, ':post_id' => $postId, ':user_id' => $userId]);
        }

        $_SESSION['success'] = "Post successfully updated!";
        header("Location: /posts");
        exit;
    }
}