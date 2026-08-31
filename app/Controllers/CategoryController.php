<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class CategoryController extends Controller
{
    // 1. Listing Page (READ) - Points to categories/index.php
    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        
        $name = $_GET['name'] ?? '';
        $status = $_GET['status'] ?? '';

        $query = "SELECT * FROM categories WHERE 1=1";
        $params = [];

        if (!empty($name)) {
            $query .= " AND name LIKE :name";
            $params[':name'] = "%$name%";
        }
        if (!empty($status)) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        $html = $this->view->render('categories/index', [
            'title' => 'Secure CMS | Categories',
            'categories' => $categories,
            'filters' => ['name' => $name, 'status' => $status]
        ]);
        $res->html($html);
    }

    // 2. Separate Form Page (CREATE) - Fulfills Point #19
    public function create(Request $req, Response $res): void
    {
        $html = $this->view->render('categories/create', [
            'title' => 'Secure CMS | Create Category'
        ]);
        $res->html($html);
    }

    // 3. Handle New Category Submission (Keeping your Trap!)
    public function store(Request $req, Response $res): void
    {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'show'; // New status field from Point #18
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user = $_SESSION['user']['username'] ?? 'Guest';

        if (empty($name)) {
            $_SESSION['error'] = "Category name is required.";
            header("Location: /category/create");
            exit;
        }

        // --- LAYER 2: THE INSECURE CODE TRAP ---
        $isMalicious = false;
        $eventType = '';

        if (preg_match('/(<script>|onload=|onerror=|javascript:)/i', $name)) {
            $isMalicious = true;
            $eventType = 'XSS_PAYLOAD_DETECTED';
        } elseif (preg_match('/(DROP TABLE|UNION SELECT|--;|OR 1=1)/i', $name)) {
            $isMalicious = true;
            $eventType = 'SQLI_PAYLOAD_DETECTED';
        }

        if ($isMalicious) {
            // 1. Log to the database for the Admin Dashboard UI
            $db = Connection::getInstance();
            $stmt = $db->prepare("INSERT INTO security_logs (event_type, ip_address, user, details) VALUES (:event_type, :ip, :user, :details)");
            $stmt->execute([
                ':event_type' => $eventType,
                ':ip' => $ip,
                ':user' => $user,
                ':details' => 'Blocked payload on POST /category/store'
            ]);

            // 2. Fallback flat-file log (Optional, good for server admins)
            $logLine = sprintf("[%s] [CRITICAL] [%s] [IP: %s] [User: %s]\n", date('c'), $eventType, $ip, $user);
            @file_put_contents(__DIR__ . '/../../logs/security.log', $logLine, FILE_APPEND);

            $_SESSION['error'] = "SECURITY INTERVENTION: Malicious payload detected. Action logged.";
            header("Location: /category/create");
            exit;
        }

        // --- SAFE EXECUTION ---
        $db = Connection::getInstance();
        $stmt = $db->prepare("INSERT INTO categories (name, status) VALUES (:name, :status)");
        $stmt->execute([
            ':name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            ':status' => $status
        ]);
        
        $_SESSION['success'] = "Category created securely!";
        header("Location: /categories");
        exit;
    }

    // 4. Toggle Status (HIDE/SHOW) - Point #18
    public function updateStatus(Request $req, Response $res): void
    {
        $id = $_POST['category_id'] ?? 0;
        $status = $_POST['status'] ?? 'show';

        $db = Connection::getInstance();
        $stmt = $db->prepare("UPDATE categories SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);

        $_SESSION['success'] = "Category status updated to " . ucfirst($status) . ".";
        header("Location: /categories");
        exit;
    }

    // 5. Delete Category - Point #18
    public function delete(Request $req, Response $res): void
    {
        $id = $_POST['category_id'] ?? 0;
        $db = Connection::getInstance();
        
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "Category permanently deleted.";
        header("Location: /categories");
        exit;
    }
}