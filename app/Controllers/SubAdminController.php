<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class SubAdminController extends Controller
{
    // 1. List all Sub-Admins (Adnan's Requirement #2)
    public function index(Request $req, Response $res): void
    {
        // Security Check: Only Super Admins can manage Sub-Admins
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        if (!in_array($role, ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Access Denied: You do not have permission to manage Sub-Admins.";
            header("Location: /dashboard");
            exit;
        }

        $db = Connection::getInstance();
        $stmt = $db->query("SELECT id, username, email, permissions, created_at FROM users WHERE role = 'sub_admin' ORDER BY created_at DESC");
        $subadmins = $stmt->fetchAll();

        $html = $this->view->render('subadmins', [
            'title' => 'Manage Sub-Admins',
            'subadmins' => $subadmins
        ]);
        $res->html($html);
    }

    // 2. Show the Create Form (The view we just built)
    public function create(Request $req, Response $res): void
    {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        if (!in_array($role, ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Access Denied.";
            header("Location: /dashboard");
            exit;
        }

        $html = $this->view->render('create_subadmin', [
            'title' => 'Create Sub-Admin'
        ]);
        $res->html($html);
    }

    // 3. Process and Save the Sub-Admin
    public function store(Request $req, Response $res): void
    {
        $role = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        if (!in_array($role, ['admin', 'super admin', 'super_admin'])) {
            $_SESSION['error'] = "Access Denied.";
            header("Location: /dashboard");
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Grab the array of checked boxes, or default to an empty array if none were checked
        $permissionsArray = $_POST['permissions'] ?? [];

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Username, email, and password are required.";
            header("Location: /subadmin/create");
            exit;
        }

        $db = Connection::getInstance();

        // Check for duplicate email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "A user with that email already exists.";
            header("Location: /subadmin/create");
            exit;
        }

        // 1. Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        
        // 2. Convert the checkboxes array into a clean JSON string
        $permissionsJson = json_encode($permissionsArray);

        // 3. Insert into database with the 'sub_admin' role
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, role, permissions) 
            VALUES (:username, :email, :password, 'sub_admin', :permissions)
        ");
        
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':permissions' => $permissionsJson
        ]);

        $_SESSION['success'] = "Sub-Admin account created successfully with custom permissions!";
        header("Location: /subadmins");
        exit;
    }
}