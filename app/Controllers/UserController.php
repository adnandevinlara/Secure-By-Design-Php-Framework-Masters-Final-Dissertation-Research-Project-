<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class UserController extends Controller
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
        extract($this->getAcl());

        // 1. ACL Hard-Block: Must have view_users permission
        if (!$isAdmin && !($isSubAdmin && in_array('view_users', $perms))) {
            $_SESSION['error'] = "Access Denied: Missing 'View Users' permission.";
            header("Location: /dashboard");
            exit;
        }

        // Grab the active database connection
        $db = Connection::getInstance();

        // 2. Fetch users securely. 
        // SECURITY: Hide Super Admins from Sub-Admins so they cannot target them.
        if ($isAdmin) {
            $stmt = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        } else {
            $stmt = $db->query("SELECT id, username, email, role, created_at FROM users WHERE role NOT IN ('admin', 'administrator', 'super admin', 'super_admin') ORDER BY created_at DESC");
        }
        
        $users = $stmt->fetchAll();

        // Pass the data to the view
        $html = $this->view->render('users', [
            'title' => 'Secure CMS | Manage Users',
            'users' => $users
        ]);

        $res->html($html);
    }

    // Securely delete a user with ACL enforcement
    public function delete(Request $req, Response $res): void
    {
        extract($this->getAcl());

        // 1. ACL Hard-Block: Must have delete_users permission
        if (!$isAdmin && !($isSubAdmin && in_array('delete_users', $perms))) {
            $_SESSION['error'] = "Access Denied: Missing 'Delete Users' permission.";
            header("Location: /users");
            exit;
        }

        $userIdToDelete = $_POST['user_id'] ?? null;
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Ensure an ID was provided and the admin isn't trying to delete themselves
        if ($userIdToDelete && $userIdToDelete != $currentUserId) {
            $db = Connection::getInstance();
            
            // 2. Extra Security: Hard-lock the database query so Sub-Admins cannot delete Super Admins
            if ($isAdmin) {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userIdToDelete]);
            } else {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role NOT IN ('admin', 'administrator', 'super admin', 'super_admin')");
                $stmt->execute([$userIdToDelete]);
            }
            
            // 3. Verify that the deletion actually happened
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "User successfully deleted.";
            } else {
                $_SESSION['error'] = "Action denied. User not found or you lack permission to delete them.";
            }
        } else {
            $_SESSION['error'] = "Action denied. You cannot delete this user.";
        }

        // Redirect back to the users table
        header("Location: /users");
        exit;
    }
}