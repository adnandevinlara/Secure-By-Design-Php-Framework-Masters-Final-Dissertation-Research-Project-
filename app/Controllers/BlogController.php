<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class BlogController extends Controller
{
    // 1. Show the Blog Posts Management Dashboard
    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $userId = $_SESSION['user']['id'] ?? 0;
        $isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';

        $categories = [];
        try {
            $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
        } catch (\PDOException $e) {}

        // RBAC: Admins see everything. Users see only their own posts.
        $query = "
            SELECT p.*, c.name AS category_name 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1
        ";
        
        $params = [];

        if (!$isAdmin) {
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

    // 2. Update Status (Publish/Hide)
    public function updateStatus(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'published';
        $userId = $_SESSION['user']['id'] ?? 0;
        $isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';

        $db = Connection::getInstance();
        
        // RBAC Check
        if ($isAdmin) {
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

    // 3. Delete a post
    public function delete(Request $req, Response $res): void
    {
        $postId = $_POST['post_id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        $isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';

        $db = Connection::getInstance();
        
        // RBAC Check
        if ($isAdmin) {
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

    // 4. Show Create Post Form
    public function create(Request $req, Response $res): void 
    {
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

    // 5. Process New Post
    public function store(Request $req, Response $res): void 
    {
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

    // 6. Show Edit Post Form
    public function edit(Request $req, Response $res): void 
    {
        $db = Connection::getInstance();
        $postId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user']['id'] ?? 0;
        $isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';

        if ($isAdmin) {
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

    // 7. Process Post Update
    public function update(Request $req, Response $res): void 
    {
        $postId = $_POST['post_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $status = $_POST['status'] ?? 'published';
        $content = trim($_POST['content'] ?? '');
        $userId = $_SESSION['user']['id'] ?? 0;
        $isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';

        $db = Connection::getInstance();

        if ($isAdmin) {
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