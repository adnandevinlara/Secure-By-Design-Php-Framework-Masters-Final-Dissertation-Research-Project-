<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class CategoryController extends Controller
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

    // Centralized Security Check
    private function checkAccess() {
        extract($this->getAcl());
        
        // 1. Block Regular Users completely
        if (!$isAdmin && !$isSubAdmin) {
            $_SESSION['error'] = "Access Denied: Only administrators can manage categories.";
            header("Location: /dashboard");
            exit;
        }
        
        // 2. Block SubAdmins if they don't have specific category permissions
        // (Since we didn't add this to the UI, ALL SubAdmins will currently be blocked from categories!)
        if ($isSubAdmin && !in_array('manage_categories', $perms)) {
            $_SESSION['error'] = "Access Denied: You are restricted from accessing the Categories module.";
            header("Location: /dashboard");
            exit;
        }
    }

    // 1. Listing Page (READ)
    public function index(Request $req, Response $res): void
    {
        $this->checkAccess(); // 🔒 SECURITY LOCK

        $db = Connection::getInstance();
        $name = $_GET['name'] ?? '';
        $status = $_GET['status'] ?? '';

        $query = "SELECT * FROM categories WHERE is_deleted = 0";
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

    // 2. Separate Form Page (CREATE)
    public function create(Request $req, Response $res): void
    {
        $this->checkAccess(); // 🔒 SECURITY LOCK

        $html = $this->view->render('categories/create', [
            'title' => 'Secure CMS | Create Category'
        ]);
        $res->html($html);
    }

    // 3. Handle New Category Submission
    public function store(Request $req, Response $res): void
    {
        $this->checkAccess(); // 🔒 SECURITY LOCK

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'show'; 
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
            $db = Connection::getInstance();
            $stmt = $db->prepare("INSERT INTO security_logs (event_type, ip_address, user, details, severity, request_url, description, timestamp) VALUES (:event_type, :ip, :user, :details, :severity, :request_url, :description, :timestamp)");
            $stmt->execute([
                ':event_type' => $eventType,
                ':ip' => $ip,
                ':user' => $user,
                ':details' => 'Blocked payload on POST /category/store',
                ':severity' => 'CRITICAL',
                ':request_url' => $_SERVER['REQUEST_URI'] ?? '/category/store',
                ':description' => 'Blocked malicious payload on Category creation',
                ':timestamp' => date('Y-m-d H:i:s')
            ]);

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

    // 4. Toggle Status (HIDE/SHOW)
    public function updateStatus(Request $req, Response $res): void
    {
        $this->checkAccess(); // 🔒 SECURITY LOCK

        $id = $_POST['category_id'] ?? 0;
        $status = $_POST['status'] ?? 'show';

        $db = Connection::getInstance();
        $stmt = $db->prepare("UPDATE categories SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);

        $_SESSION['success'] = "Category status updated to " . ucfirst($status) . ".";
        header("Location: /categories");
        exit;
    }

    // 5. Delete Category (Soft Delete)
    public function delete(Request $req, Response $res): void
    {
        $this->checkAccess(); // 🔒 SECURITY LOCK

        $id = $_POST['category_id'] ?? 0;
        $db = Connection::getInstance();
        
        $stmt = $db->prepare("UPDATE categories SET is_deleted = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "Category deleted successfully.";
        header("Location: /categories");
        exit;
    }
}