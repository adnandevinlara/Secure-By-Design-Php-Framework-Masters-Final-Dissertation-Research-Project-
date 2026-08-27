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
        
        // Normalize the role text
        $userRole = strtolower(trim($_SESSION['user']['role'] ?? 'user'));
        $userId = $_SESSION['user']['id'] ?? 0;

        // If the user is an Admin or Sub-Admin, load the main admin dashboard with ACL
        if (in_array($userRole, ['admin', 'administrator', 'super admin', 'super_admin', 'sub_admin'])) {
            $this->loadAdminDashboard($db, $res, $userRole);
            return;
        }

        // --- REGISTERED USER DASHBOARD LOGIC (Remains unchanged for normal users) ---
        
        // 1. Fetch User Stats
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

        // 2. Fetch Pending Comments for the Table
        $stmt = $db->prepare("
            SELECT c.*, p.title as post_title 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            WHERE p.author_id = ? AND c.status = 'pending' 
            ORDER BY c.created_at DESC LIMIT 5
        ");
        $stmt->execute([$userId]);
        $pending_comments = $stmt->fetchAll();

        // 3. Dynamic Chart Data
        $chart_data = [0, 0, 0, 0, 0, 0, 0];

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

    // --- ADMIN / SUB-ADMIN DASHBOARD LOGIC (Now with ACL!) ---
    private function loadAdminDashboard($db, $res, $userRole): void 
    {
        $isAdmin = in_array($userRole, ['admin', 'administrator', 'super admin', 'super_admin']);
        $isSubAdmin = $userRole === 'sub_admin';
        $perms = $_SESSION['user']['permissions'] ?? [];

        $stats = [
            'total_users' => 0,
            'total_posts' => 0,
            'total_comments' => 0,
            'total_alerts' => 0
        ];
        
        $posts = [];
        $recent_comments = [];
        $recent_logs = [];

        // ACL: View Users
        if ($isAdmin || ($isSubAdmin && in_array('view_users', $perms))) {
            $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        }

        // ACL: View Blogs
        if ($isAdmin || ($isSubAdmin && in_array('view_blogs', $perms))) {
            $stats['total_posts'] = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
            $posts = $db->query("SELECT title, content FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
        }

        // ACL: View Comments
        if ($isAdmin || ($isSubAdmin && in_array('view_comments', $perms))) {
            $stats['total_comments'] = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
            $recent_comments = $db->query("SELECT author, content FROM comments ORDER BY created_at DESC LIMIT 5")->fetchAll();
        }

        // ACL: View Security Alerts
        if ($isAdmin || ($isSubAdmin && in_array('view_security', $perms))) {
            if ($this->tableExists($db, 'security_logs')) {
                $stats['total_alerts'] = $db->query("SELECT COUNT(*) FROM security_logs")->fetchColumn();
                $recent_logs = $db->query("SELECT event_type, ip_address FROM security_logs ORDER BY timestamp DESC LIMIT 5")->fetchAll();
            }
        }

        $html = $this->view->render('dashboard', [
            'title' => 'Secure CMS | Dashboard Overview',
            'stats' => $stats,
            'posts' => $posts,
            'recent_comments' => $recent_comments,
            'recent_logs' => $recent_logs,
            'isAdmin' => $isAdmin,
            'isSubAdmin' => $isSubAdmin,
            'perms' => $perms
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