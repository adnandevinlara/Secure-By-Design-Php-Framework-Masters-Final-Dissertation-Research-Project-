<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class UserController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        // Grab the active database connection
        $db = \Core\Database\Connection::getInstance();

        // Fetch all users securely (Notice we DO NOT select the password_hash column for security)
        $stmt = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        // Pass the data to the view
        $html = $this->view->render('users', [
            'title' => 'Secure CMS | Manage Users',
            'users' => $users
        ]);

        $res->html($html);
    }

    // NEW: Securely delete a user
    public function delete(Request $req, Response $res): void
    {
        $userIdToDelete = $_POST['user_id'] ?? null;
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Ensure an ID was provided and the admin isn't trying to delete themselves
        if ($userIdToDelete && $userIdToDelete != $currentUserId) {
            $db = \Core\Database\Connection::getInstance();
            
            // Secure Prepared Statement
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userIdToDelete]);
            
            $_SESSION['success'] = "User successfully deleted.";
        } else {
            $_SESSION['error'] = "Action denied. You cannot delete this user.";
        }

        // Redirect back to the users table
        header("Location: /users");
        exit;
    }
}