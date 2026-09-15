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

        // 1. ACL Hard-Block: Must be Admin or SubAdmin
        if (!$isAdmin && !$isSubAdmin) {
            $_SESSION['error'] = "Access Denied: Only administrators can view this page.";
            header("Location: /dashboard");
            exit;
        }

        $db = Connection::getInstance();

        $statusFilter = $_GET['status'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $params = [];

        // Base query - Added is_active to the SELECT statement
        $query = "SELECT id, username, email, role, is_active, created_at FROM users WHERE 1=1";

        // SECURITY: Hide Super Admins from Sub-Admins
        if (!$isAdmin) {
            $query .= " AND role NOT IN ('admin', 'administrator', 'super admin', 'super_admin')";
        }

        // Apply Status Filter
        if ($statusFilter !== '') {
            $query .= " AND is_active = ?";
            $params[] = (int) $statusFilter;
        }

        // Apply Role Filter (Ensure SubAdmins can't trick the filter into showing SuperAdmins)
        if ($roleFilter !== '') {
            if ($isAdmin || !in_array(strtolower($roleFilter), ['admin', 'super admin', 'super_admin'])) {
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

    // Securely delete a user with Cascading Data/Image Deletion
    public function delete(Request $req, Response $res): void
    {
        extract($this->getAcl());

        // 1. ACL Hard-Block
        if (!$isAdmin && !$isSubAdmin) {
            $_SESSION['error'] = "Access Denied: Only administrators can delete users.";
            header("Location: /users");
            exit;
        }

        $userIdToDelete = (int) ($_POST['user_id'] ?? 0);
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

        // 2. SELF-DELETION SAFEGUARD
        if ($userIdToDelete === $currentUserId || $userIdToDelete === 0) {
            $_SESSION['error'] = "Security Exception: You cannot delete your own account.";
            header("Location: /users");
            exit;
        }

        $db = Connection::getInstance();

        // 3. Prevent Sub-Admins from deleting Super Admins
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userIdToDelete]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            $_SESSION['error'] = "User not found.";
            header("Location: /users");
            exit;
        }

        if (!$isAdmin && in_array(strtolower($targetUser['role']), ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Security Exception: Sub-Admins cannot delete Super Admin accounts.";
            header("Location: /users");
            exit;
        }

        // 4. CASCADING IMAGE DELETION (Freeing Server Space)
        $stmtPosts = $db->prepare("SELECT banner_image FROM posts WHERE author_id = ?");
        $stmtPosts->execute([$userIdToDelete]);
        $posts = $stmtPosts->fetchAll();

        foreach ($posts as $post) {
            if (!empty($post['banner_image'])) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($post['banner_image'], '/');
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Delete actual file from server
                }
            }
        }

        // 5. CASCADING DATABASE DELETION
        // Delete comments belonging to the user's posts first
        $db->prepare("DELETE FROM comments WHERE post_id IN (SELECT id FROM posts WHERE author_id = ?)")->execute([$userIdToDelete]);
        
        // Delete their categories (Using author_id, with a safety net)
        try {
            $db->prepare("DELETE FROM categories WHERE author_id = ?")->execute([$userIdToDelete]);
        } catch (\PDOException $e) {
            // Safely ignore if the categories table does not track user ownership
        }

        // Delete their posts
        $db->prepare("DELETE FROM posts WHERE author_id = ?")->execute([$userIdToDelete]);
        
        // Finally, delete the user
        $stmtUser = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmtUser->execute([$userIdToDelete]);

        $_SESSION['success'] = "User and all associated data (posts, comments, categories, and images) successfully deleted.";
        header("Location: /users");
        exit;
    }

    // Securely Activate/Deactivate users
    public function toggleStatus(Request $req, Response $res) 
    {
        extract($this->getAcl());

        // 1. ACL Hard-Block 
        if (!$isAdmin && !$isSubAdmin) {
            $_SESSION['error'] = "Access Denied: Only administrators can change status.";
            header("Location: /users");
            exit;
        }

        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
        $newStatus = (int) ($_POST['is_active'] ?? 1); 

        // 1. SELF-DEACTIVATION SAFEGUARD
        if ($targetUserId === $currentUserId || $targetUserId === 0) {
            $_SESSION['error'] = "Security Exception: You cannot deactivate your own account.";
            header("Location: /users");
            exit;
        }

        $db = Connection::getInstance();

        // 2. Prevent Sub-Admins from deactivating Super Admins
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$isAdmin && in_array(strtolower($targetUser['role']), ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Security Exception: Sub-Admins cannot deactivate Super Admin accounts.";
            header("Location: /users");
            exit;
        }

        // 3. Update the user status
        $stmtUpdate = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmtUpdate->execute([$newStatus, $targetUserId]);

        $statusText = $newStatus === 1 ? "Activated" : "Deactivated";
        $_SESSION['success'] = "User account successfully {$statusText}.";
        
        header("Location: /users");
        exit;
    }

    // Show Change Password Form
    public function changePasswordForm(Request $req, Response $res): void
    {
        $html = $this->view->render('change_password', [
            'title' => 'Secure CMS | Change Password'
        ]);
        $res->html($html);
    }

    // Process Password Change
    public function updatePassword(Request $req, Response $res): void
    {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 0;

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

        // Verify current password
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['error'] = "Incorrect current password.";
            header("Location: /change-password");
            exit;
        }

        // Hash and save new password
        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
        $updateStmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $updateStmt->execute([':password' => $hashedPassword, ':id' => $userId]);

        $_SESSION['success'] = "Password successfully updated!";
        header("Location: /dashboard"); // Send them back to their dashboard
        exit;
    }
}