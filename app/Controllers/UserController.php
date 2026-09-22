<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class UserController extends Controller
{
    private function getAcl() {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        return [
            'isAdmin' => in_array($role, ['admin', 'super admin', 'super_admin', 'administrator']),
            'isSubAdmin' => $role === 'sub_admin',
            'isUser' => $role === 'user',
            'perms' => $_SESSION['user']['permissions'] ?? [],
            'userId' => (int)($_SESSION['user']['id'] ?? 0)
        ];
    }

    // Strict Centralized RBAC
    private function checkAccess($requiredAction = 'view_users') {
        extract($this->getAcl());
        
        if ($userId === 0) {
            $_SESSION['error'] = "Session expired.";
            header("Location: /login");
            exit;
        }

        if ($isAdmin) return true;
        
        if ($isSubAdmin) {
            if (!in_array('view_users', $perms)) {
                $_SESSION['error'] = "Access Denied: You are restricted from accessing the User Management module.";
                header("Location: /dashboard");
                exit;
            }
            if ($requiredAction !== 'view_users' && !in_array($requiredAction, $perms)) {
                $_SESSION['error'] = "Access Denied: You do not have permission for this specific action.";
                header("Location: /users");
                exit;
            }
            return true;
        }
        
        // Standard Users completely blocked
        $_SESSION['error'] = "Security Exception: Standard users cannot access the admin user module.";
        header("Location: /dashboard");
        exit;
    }

    public function index(Request $req, Response $res): void
    {
        $this->checkAccess('view_users');
        extract($this->getAcl());
        $db = Connection::getInstance();

        $statusFilter = $_GET['status'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $params = [];

        $query = "SELECT id, username, email, role, is_active, created_at FROM users WHERE 1=1";

        if (!$isAdmin) {
            $query .= " AND role NOT IN ('admin', 'administrator', 'super admin', 'super_admin')";
        }

        if ($statusFilter !== '') {
            $query .= " AND is_active = ?";
            $params[] = (int) $statusFilter;
        }

        if ($roleFilter !== '') {
            $normalizedRole = strtolower(trim($roleFilter));
            if (in_array($normalizedRole, ['superadmin', 'admin', 'super_admin', 'super admin'])) {
                if ($isAdmin) { 
                    $query .= " AND role IN ('admin', 'super_admin', 'super admin', 'administrator')";
                }
            } 
            elseif (in_array($normalizedRole, ['subadmin', 'sub_admin', 'sub admin'])) {
                $query .= " AND role IN ('sub_admin', 'sub admin')";
            } 
            elseif ($normalizedRole === 'user') {
                $query .= " AND role = 'user'";
            } 
            else {
                $query .= " AND role = ?";
                $params[] = $roleFilter;
            }
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $html = $this->view->render('users', [
            'title' => 'Secure CMS | Manage Users',
            'users' => $users
        ]);

        $res->html($html);
    }

    public function delete(Request $req, Response $res): void
    {
        $this->checkAccess('delete_users');
        extract($this->getAcl());

        $userIdToDelete = (int) ($_POST['user_id'] ?? 0);
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

        if ($userIdToDelete === $currentUserId || $userIdToDelete === 0) {
            $_SESSION['error'] = "Security Exception: You cannot delete your own account.";
            header("Location: /users");
            exit;
        }

        $db = Connection::getInstance();

        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userIdToDelete]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            $_SESSION['error'] = "User not found.";
            header("Location: /users");
            exit;
        }

        if (!$isAdmin && in_array(strtolower($targetUser['role']), ['admin', 'super admin', 'super_admin', 'administrator'])) {
            $_SESSION['error'] = "Security Exception: Sub-Admins cannot delete Super Admin accounts.";
            header("Location: /users");
            exit;
        }

        if (in_array(strtolower($targetUser['role']), ['sub_admin', 'sub admin', 'subadmin'])) {
            $db->prepare("UPDATE posts SET author_id = ? WHERE author_id = ?")->execute([$currentUserId, $userIdToDelete]);
            try { $db->prepare("UPDATE comments SET user_id = ? WHERE user_id = ?")->execute([$currentUserId, $userIdToDelete]); } catch (\PDOException $e) {}
            try { $db->prepare("UPDATE comments SET author_id = ? WHERE author_id = ?")->execute([$currentUserId, $userIdToDelete]); } catch (\PDOException $e) {}
            try { $db->prepare("UPDATE categories SET user_id = ? WHERE user_id = ?")->execute([$currentUserId, $userIdToDelete]); } catch (\PDOException $e) {}
            try { $db->prepare("UPDATE categories SET author_id = ? WHERE author_id = ?")->execute([$currentUserId, $userIdToDelete]); } catch (\PDOException $e) {}

            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userIdToDelete]);

            $_SESSION['success'] = "SubAdmin deleted successfully. All their posts, comments, and categories have been reassigned to you.";
            header("Location: /users");
            exit;
        }

        $stmtPosts = $db->prepare("SELECT banner_image FROM posts WHERE author_id = ?");
        $stmtPosts->execute([$userIdToDelete]);
        $posts = $stmtPosts->fetchAll();

        foreach ($posts as $post) {
            if (!empty($post['banner_image'])) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($post['banner_image'], '/');
                if (file_exists($imagePath)) unlink($imagePath);
            }
        }

        $db->prepare("DELETE FROM comments WHERE post_id IN (SELECT id FROM posts WHERE author_id = ?)")->execute([$userIdToDelete]);
        try { $db->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$userIdToDelete]); } catch (\PDOException $e) { }
        try { $db->prepare("DELETE FROM categories WHERE user_id = ?")->execute([$userIdToDelete]); } catch (\PDOException $e) { }
        $db->prepare("DELETE FROM posts WHERE author_id = ?")->execute([$userIdToDelete]);
        
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userIdToDelete]);

        $_SESSION['success'] = "User and all associated data (posts, comments, categories, and images) successfully deleted.";
        header("Location: /users");
        exit;
    }

    public function toggleStatus(Request $req, Response $res) 
    {
        $this->checkAccess('edit_users');
        extract($this->getAcl());

        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
        $newStatus = (int) ($_POST['is_active'] ?? 1); 

        if ($targetUserId === $currentUserId || $targetUserId === 0) {
            $_SESSION['error'] = "Security Exception: You cannot deactivate your own account.";
            header("Location: /users");
            exit;
        }

        $db = Connection::getInstance();

        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$isAdmin && in_array(strtolower($targetUser['role']), ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Security Exception: Sub-Admins cannot deactivate Super Admin accounts.";
            header("Location: /users");
            exit;
        }

        $stmtUpdate = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmtUpdate->execute([$newStatus, $targetUserId]);

        $_SESSION['success'] = "User account successfully " . ($newStatus === 1 ? "Activated" : "Deactivated") . ".";
        header("Location: /users");
        exit;
    }

    public function changePasswordForm(Request $req, Response $res): void
    {
        $html = $this->view->render('change_password', [
            'title' => 'Secure CMS | Change Password'
        ]);
        $res->html($html);
    }

    public function updatePassword(Request $req, Response $res): void
    {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = (int) ($_SESSION['user']['id'] ?? 0);

        if ($userId === 0) {
            $_SESSION['error'] = "Session expired. Please log in again.";
            header("Location: /login");
            exit;
        }

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /change-password");
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New passwords do not match.";
            header("Location: /change-password");
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = "New password must be at least 8 characters long.";
            header("Location: /change-password");
            exit;
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['error'] = "Incorrect current password.";
            header("Location: /change-password");
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
        $updateStmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $updateStmt->execute([':password' => $hashedPassword, ':id' => $userId]);

        $_SESSION['success'] = "Password successfully updated!";
        $_SESSION['password_updated_modal'] = "Password updated successfully"; 
        
        header("Location: /change-password"); 
        exit;
    }
}