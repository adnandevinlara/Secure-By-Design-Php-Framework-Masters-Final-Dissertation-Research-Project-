<?php

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class HomeController
{
    private $view;

    public function __construct()
    {
        $this->view = new \Core\View\Engine();
    }

    // 1. Load the main blog feed (Updated with Pagination & 'Published' filter)
    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();

        // Pagination setup (Adnan's Requirement #14)
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5; // Number of posts per page
        $offset = ($page - 1) * $limit;

        // Get total number of published posts to calculate total pages
        $totalPosts = $db->query("SELECT COUNT(id) FROM posts WHERE status = 'published'")->fetchColumn();
        $totalPages = ceil($totalPosts / $limit);

        // Fetch ONLY published posts with limit and offset
        $stmt = $db->prepare("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.status = 'published'
            ORDER BY posts.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        // Bind parameters specifically as integers for SQLite LIMIT/OFFSET
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();

        // Fetch categories for the public sidebar
        $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

        // Render the public home view
        $html = $this->view->render('home', [
            'title' => 'Secure Blog | Home',
            'posts' => $posts,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
        
        $res->html($html);
    }

    // 2. Handle the Search functionality (Updated to only search 'Published' posts)
    public function search(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $query = trim($_GET['q'] ?? '');

        // Securely search ONLY published posts
        $stmt = $db->prepare("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.status = 'published' AND (posts.title LIKE :q OR posts.content LIKE :q)
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute(['q' => "%$query%"]);
        $posts = $stmt->fetchAll();

        // Fetch categories for the public sidebar
        $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

        $html = $this->view->render('home', [
            'title' => 'Search Results: ' . htmlspecialchars($query),
            'posts' => $posts,
            'categories' => $categories,
            'currentPage' => 1,
            'totalPages' => 1, // Simplified pagination for search results
            'searchQuery' => $query
        ]);
        $res->html($html);
    }

    // 3. Handle viewing a single Post Detail page
    public function show(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $res->html("<h1>404 - Post Not Found</h1>");
            return;
        }

        // Securely fetch a single post (Must be published!)
        $stmt = $db->prepare("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.id = :id AND posts.status = 'published'
        ");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post) {
            $res->html("<h1>404 - Post Not Found or Not Published</h1>");
            return;
        }

        // Fetch APPROVED comments for this specific post
        $stmtComments = $db->prepare("
            SELECT author, content, created_at 
            FROM comments 
            WHERE post_id = :post_id AND status = 'approved'
            ORDER BY created_at ASC
        ");
        $stmtComments->execute(['post_id' => $id]);
        $approvedComments = $stmtComments->fetchAll();

        // Fetch categories for the public sidebar
        $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

        $html = $this->view->render('post_details', [
            'title' => htmlspecialchars($post['title']),
            'post' => $post,
            'comments' => $approvedComments,
            'categories' => $categories
        ]);
        $res->html($html);
    }
}