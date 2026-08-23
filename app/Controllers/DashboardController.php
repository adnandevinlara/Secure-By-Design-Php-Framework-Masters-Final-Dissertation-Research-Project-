<?php

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Core\Database\Connection;

class DashboardController
{
    private $view;

    public function __construct()
    {
        $this->view = new \Core\View\Engine();
    }

    public function index(Request $req, Response $res): void
    {
        $db = Connection::getInstance();
        
        // Check the logged-in user's role and ID from the session
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $userId = $_SESSION['user']['id'] ?? 0;

        // If the user is an Admin, load the original admin dashboard
        if ($userRole === 'admin' || $userRole === 'administrator') {
            $this->loadAdminDashboard($db, $res);
            return;
        }

        // --- REGISTERED USER DASHBOARD LOGIC ---
        
        // 1. Fetch User Stats (Execute the queries directly for SQLite)
        $stmtPosts = $db->prepare("SELECT COUNT(*) FROM posts WHERE author_id = ?");
        $stmtPosts->execute([$userId]);
        $userPosts = $stmtPosts->fetchColumn();

        $stmtComments = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtComments->execute([$userId]);
        $userComments = $stmtComments->fetchColumn();

        $stmtApproved = $db->prepare("SELECT COUNT(*) FROM comments WHERE status = 'approved' AND post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtApproved->execute([$userId]);
        $approvedComments = $stmtApproved->fetchColumn();

        $stmtPending = $db->prepare("SELECT COUNT(*) FROM comments WHERE status = 'pending' AND post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtPending->execute([$userId]);
        $pendingComments = $stmtPending->fetchColumn();

        $statValues = [
            'user_posts' => $userPosts,
            'user_comments' => $userComments,
            'approved_comments' => $approvedComments,
            'pending_comments' => $pendingComments
        ];

        // 2. Fetch Pending Comments for the Table (Comments on THIS user's posts)
        $stmt = $db->prepare("
            SELECT c.*, p.title as post_title 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            WHERE p.author_id = ? AND c.status = 'pending' 
            ORDER BY c.created_at DESC LIMIT 5
        ");
        $stmt->execute([$userId]);
        $pending_comments = $stmt->fetchAll();

        // 3. Dynamic Chart Data (Comments received on user's posts this week)
        $chart_data = [0, 0, 0, 0, 0, 0, 0]; // Default Mon-Sun to 0

        $stmtChart = $db->prepare("
            SELECT strftime('%w', c.created_at) as day_of_week, COUNT(*) as count 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            WHERE p.author_id = ? 
              AND c.created_at >= date('now', 'weekday 0', '-7 days')
            GROUP BY day_of_week
        ");
        $stmtChart->execute([$userId]);
        $chartResults = $stmtChart->fetchAll();

        // Map SQLite days (0=Sun, 1=Mon) to our Chart array (0=Mon, 6=Sun)
        foreach ($chartResults as $row) {
            $dayIndex = (int)$row['day_of_week'];
            $map = [1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 0 => 6];
            if (isset($map[$dayIndex])) {
                $chart_data[$map[$dayIndex]] = (int)$row['count'];
            }
        }

        $html = $this->view->render('user_dashboard', [
            'title' => 'My Dashboard',
            'user' => $_SESSION['user'] ?? ['username' => 'User'],
            'stats' => $statValues,
            'pending_comments' => $pending_comments,
            'chart_data' => $chart_data
        ]);

        $res->html($html);
    }

    // --- ADMIN DASHBOARD LOGIC ---
    private function loadAdminDashboard($db, $res): void 
    {
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_posts' => $db->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
            'total_comments' => $db->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
            'total_alerts' => $this->tableExists($db, 'security_logs') 
                ? $db->query("SELECT COUNT(*) FROM security_logs")->fetchColumn() 
                : 0
        ];

        $posts = $db->query("SELECT title, content FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $recent_comments = $db->query("SELECT author, content FROM comments ORDER BY created_at DESC LIMIT 5")->fetchAll();
        
        $recent_logs = [];
        if ($this->tableExists($db, 'security_logs')) {
            $recent_logs = $db->query("SELECT event_type, ip_address FROM security_logs ORDER BY timestamp DESC LIMIT 5")->fetchAll();
        }

        $html = $this->view->render('dashboard', [
            'title' => 'Secure CMS | Dashboard Overview',
            'stats' => $stats,
            'posts' => $posts,
            'recent_comments' => $recent_comments,
            'recent_logs' => $recent_logs
        ]);

        $res->html($html);
    }

    private function tableExists($db, $tableName): bool
    {
        $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:name");
        $stmt->execute(['name' => $tableName]);
        return (bool) $stmt->fetch();
    }
}