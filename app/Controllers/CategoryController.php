<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class CategoryController extends Controller
{
    private function getAcl() {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'guest'));
        return [
            'isAdmin' => in_array($role, ['admin', 'super admin', 'super_admin', 'administrator']),
            'isSubAdmin' => $role === 'sub_admin',
            'isUser' => $role === 'user',
            'perms' => $_SESSION['user']['permissions'] ?? [],
            'userId' => (int) ($_SESSION['user']['id'] ?? 0)
        ];
    }

    private function checkAccess($requiredAction = 'view_categories') {
        extract($this->getAcl());
        
        if ($userId === 0) {
            $_SESSION['error'] = "Session expired. Please log in again.";
            header("Location: /login");
            exit;
        }

        if ($isAdmin) return true; 

        if ($isUser) {
            if ($requiredAction === 'edit_categories' || $requiredAction === 'delete_categories') {
                $_SESSION['error'] = "Security Exception: Standard users cannot edit or delete global categories.";
                header("Location: /categories");
                exit;
            }
            return true; 
        }
        
        if ($isSubAdmin) {
            return true; // Bypass until UI is built
        }
        
        $_SESSION['error'] = "Access Denied.";
        header("Location: /dashboard");
        exit;
    }

    public function index(Request $req, Response $res): void
    {
        $this->checkAccess('view_categories'); 
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

    public function create(Request $req, Response $res): void
    {
        $this->checkAccess('create_categories'); 
        $html = $this->view->render('categories/create', [
            'title' => 'Secure CMS | Create Category'
        ]);
        $res->html($html);
    }

    // AJAX Endpoint to check if category exists
    public function checkName(Request $req, Response $res): void
    {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(:name) AND is_deleted = 0");
        $stmt->execute([':name' => $name]);
        $count = $stmt->fetchColumn();

        echo json_encode(['exists' => $count > 0]);
        exit;
    }

    public function store(Request $req, Response $res): void
    {
        $this->checkAccess('create_categories'); 

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'show'; 
        
        if (empty($name)) {
            $_SESSION['error'] = "Category name is required.";
            header("Location: /category/create");
            exit;
        }

        $db = Connection::getInstance();

        // Server-Side Duplicate Check
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(:name) AND is_deleted = 0");
        $stmtCheck->execute([':name' => $name]);
        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION['error'] = "The category '{$name}' already exists. Please choose a different name.";
            header("Location: /category/create");
            exit;
        }

        // --- Safe Execution ---
        $stmt = $db->prepare("INSERT INTO categories (name, status) VALUES (:name, :status)");
        $stmt->execute([
            ':name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            ':status' => $status
        ]);
        
        $_SESSION['success'] = "Category created securely!";
        header("Location: /categories");
        exit;
    }

    public function updateStatus(Request $req, Response $res): void
    {
        $this->checkAccess('edit_categories'); 
        $id = $_POST['category_id'] ?? 0;
        $status = $_POST['status'] ?? 'show';

        $db = Connection::getInstance();
        $stmt = $db->prepare("UPDATE categories SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);

        $_SESSION['success'] = "Category status updated to " . ucfirst($status) . ".";
        header("Location: /categories");
        exit;
    }

    public function delete(Request $req, Response $res): void
    {
        $this->checkAccess('delete_categories'); 
        $id = $_POST['category_id'] ?? 0;
        $db = Connection::getInstance();
        
        // RULE 3: Protect categories assigned to posts
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM posts WHERE category_id = :id");
        $stmtCheck->execute([':id' => $id]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION['error'] = "Cannot delete: This category is currently assigned to one or more posts. Reassign the posts first.";
            header("Location: /categories");
            exit;
        }
        
        // Safe to delete
        $stmt = $db->prepare("UPDATE categories SET is_deleted = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "Category deleted successfully.";
        header("Location: /categories");
        exit;
    }
}