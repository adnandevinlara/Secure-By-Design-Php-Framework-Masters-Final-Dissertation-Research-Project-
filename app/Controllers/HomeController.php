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

    // 1. Load the main blog feed
    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();

        // Fetch posts with their category names and author names
        $stmt = $db->query("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            ORDER BY posts.created_at DESC
        ");
        $posts = $stmt->fetchAll();

        // Render the public home view
        $html = $this->view->render('home', [
            'title' => 'Secure Blog | Home',
            'posts' => $posts
        ]);
        
        $res->html($html);
    }

    // 2. Handle the Search functionality
    public function search(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $query = $_GET['q'] ?? '';

        // Securely search using a prepared statement to prevent SQL Injection
        $stmt = $db->prepare("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.title LIKE :q OR posts.content LIKE :q
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute(['q' => "%$query%"]);
        $posts = $stmt->fetchAll();

        $html = $this->view->render('home', [
            'title' => 'Search Results: ' . htmlspecialchars($query),
            'posts' => $posts
        ]);
        $res->html($html);
    }

    // 3. Handle viewing a single Post Detail page (Read More)
    public function show(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $res->html("<h1>404 - Post Not Found</h1>");
            return;
        }

        // Securely fetch a single post
        $stmt = $db->prepare("
            SELECT posts.*, categories.name AS category_name, users.username AS author_name 
            FROM posts 
            LEFT JOIN categories ON posts.category_id = categories.id
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post) {
            $res->html("<h1>404 - Post Not Found</h1>");
            return;
        }

        // NEW: Fetch APPROVED comments for this specific post
        $stmtComments = $db->prepare("
            SELECT author, content, created_at 
            FROM comments 
            WHERE post_id = :post_id AND status = 'approved'
            ORDER BY created_at ASC
        ");
        $stmtComments->execute(['post_id' => $id]);
        $approvedComments = $stmtComments->fetchAll();

        // Pass both the post AND the comments to the view
        $html = $this->view->render('post_details', [
            'title' => htmlspecialchars($post['title']),
            'post' => $post,
            'comments' => $approvedComments
        ]);
        $res->html($html);
    }
}