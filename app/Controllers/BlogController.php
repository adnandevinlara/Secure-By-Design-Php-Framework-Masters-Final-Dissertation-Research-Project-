<?php
namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Session;

class BlogController {

    /**
     * Securely fetches and displays all blog posts
     */
    public function index(Request $request, Response $response) {
        // Enforce Authentication: Redirect guests back to login
        if (!Session::get('user_id')) {
            Session::set('test_auth_status', 'Security Alert: You must be logged in to view the dashboard.');
            header('Location: /login');
            exit;
        }

        // Fetch posts directly using the PDO connection
        $db = \Core\Database\Connection::getInstance();
        $stmt = $db->query("SELECT * FROM posts ORDER BY id DESC");
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?? []; 

        // FIXED: Directly require the view file since Response::render doesn't exist
        $viewFile = __DIR__ . '/../Views/dashboard.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // Fallback just in case the view folder is named differently
            echo "Posts retrieved successfully, but could not locate dashboard.php view file.";
        }
    }

    /**
     * Securely processes a new blog post submission
     */
    public function store(Request $request, Response $response) {
        // 1. Enforce Authentication
        if (!Session::get('user_id')) {
            header('Location: /login');
            exit;
        }

        $data = $_POST;

        // 2. Validate Input
        if (empty($data['title']) || empty($data['content'])) {
            Session::set('test_auth_status', 'Error: Title and content are required.');
            header('Location: /dashboard');
            exit;
        }

        try {
            // Secure Database Insertion using direct PDO Prepared Statements
            $db = \Core\Database\Connection::getInstance();
            $stmt = $db->prepare("INSERT INTO posts (title, content, author_id) VALUES (:title, :content, :author_id)");
            $stmt->execute([
                ':title'     => $data['title'],
                ':content'   => $data['content'],
                ':author_id' => Session::get('user_id')
            ]);

            Session::set('test_auth_status', 'Success: Post securely published to the database.');
            
        } catch (\Exception $e) {
            Session::set('test_auth_status', 'Database Error: Could not save post.');
        }

        header('Location: /dashboard');
        exit;
    }
}