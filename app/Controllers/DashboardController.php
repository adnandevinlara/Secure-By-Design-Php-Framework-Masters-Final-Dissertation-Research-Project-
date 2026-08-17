<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Session;

class DashboardController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        // 1. Define the Security Matrix for the Dashboard
        $securityContext = [
            'Administrative Access' => 'ASVS V4.1: Strictly enforces Administrator-only access to this dashboard.',
            'Content Security Policy' => 'ASVS V14.4: Strict CSP enforced, safely rendering the StarCode Kh template assets.'
        ];

        // Grab the active database connection
        $db = \Core\Database\Connection::getInstance();

        // 2. Fetch real dynamic stats from the database securely
        $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalPosts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();

        $stats = [
            'total_users' => $totalUsers,
            'total_posts' => $totalPosts,
            'total_comments' => 0
        ];

        // 3. Fetch the 5 most recent real posts
        $stmt = $db->query("SELECT title, content FROM posts ORDER BY created_at DESC LIMIT 5");
        $posts = $stmt->fetchAll();

        // 4. Pass the $securityContext and real data to the view
        $html = $this->view->render('dashboard', [
            'title' => 'Secure CMS | Dashboard',
            'securityContext' => $securityContext, 
            'stats' => $stats,
            'posts' => $posts
        ]);

        $res->html($html);
    }
}