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
        $username = $_SESSION['user']['username'] ?? 'User';

        // If the user is an Admin or Sub-Admin, load the main admin dashboard with ACL
        if (in_array($userRole, ['admin', 'administrator', 'super admin', 'super_admin', 'sub_admin'])) {
            $this->loadAdminDashboard($db, $res, $userRole);
            return;
        }

        // --- REGISTERED USER DASHBOARD LOGIC ---

        // 1. Fetch User Stats (Data Isolated to their own posts)
        $userPosts = $db->query("SELECT COUNT(*) FROM posts WHERE author_id = $userId")->fetchColumn();

        $stmtComments = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtComments->execute([$userId]);
        $userComments = $stmtComments->fetchColumn();

        $stmtApproved = $db->prepare("SELECT COUNT(*) FROM comments WHERE status = 'approved' AND post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtApproved->execute([$userId]);
        $approvedComments = $stmtApproved->fetchColumn();

        $stmtPending = $db->prepare("SELECT COUNT(*) FROM comments WHERE status = 'pending' AND post_id IN (SELECT id FROM posts WHERE author_id = ?)");
        $stmtPending->execute([$userId]);
        $pendingComments = $stmtPending->fetchColumn();

        // 2. Fetch Pending Comments for the Recent Table
        $stmt = $db->prepare("
            SELECT c.*, p.title as post_title 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            WHERE p.author_id = ? AND c.status = 'pending' 
            ORDER BY c.created_at DESC LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recent_comments = $stmt->fetchAll();

        // 3. Dynamic Chart Data (Comments received over the last 7 days)
        $stmtChart = $db->prepare("
            SELECT date(c.created_at) as comment_date, COUNT(*) as count 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            WHERE p.author_id = ? AND c.created_at >= date('now', '-7 days')
            GROUP BY date(c.created_at)
            ORDER BY comment_date ASC
        ");
        $stmtChart->execute([$userId]);
        $chartResults = $stmtChart->fetchAll();

        // Format dates for Chart.js
        $chartLabels = [];
        $chartData = [];
        
        // Pre-fill the last 7 days with 0 so the chart always looks full
        for ($i = 6; $i >= 0; $i--) {
            $dateString = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('M d', strtotime($dateString));
            
            // Find if we have data for this date
            $count = 0;
            foreach ($chartResults as $row) {
                if ($row['comment_date'] === $dateString) {
                    $count = (int)$row['count'];
                    break;
                }
            }
            $chartData[] = $count;
        }

        $html = $this->view->render('user_dashboard', [
            'title' => 'My Dashboard',
            'username' => $username,
            'stats' => [
                'user_posts' => $userPosts,
                'user_comments' => $userComments,
                'approved_comments' => $approvedComments,
                'pending_comments' => $pendingComments,
                'account_status' => 'ACTIVE'
            ],
            'pending_comments' => $recent_comments,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData)
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

        // ACL Checks
        if ($isAdmin || ($isSubAdmin && in_array('view_users', $perms))) {
            $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        }
        if ($isAdmin || ($isSubAdmin && in_array('view_blogs', $perms))) {
            $stats['total_posts'] = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
            $posts = $db->query("SELECT title, content FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
        }
        if ($isAdmin || ($isSubAdmin && in_array('view_comments', $perms))) {
            $stats['total_comments'] = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
            $recent_comments = $db->query("SELECT author, content FROM comments ORDER BY created_at DESC LIMIT 5")->fetchAll();
        }
        if ($isAdmin || ($isSubAdmin && in_array('view_security', $perms))) {
            if ($this->tableExists($db, 'security_logs')) {
                $stats['total_alerts'] = $db->query("SELECT COUNT(*) FROM security_logs")->fetchColumn();
                $recent_logs = $db->query("SELECT event_type, ip_address FROM security_logs ORDER BY timestamp DESC LIMIT 5")->fetchAll();
            }
        }

        // ---------------------------------------------------------
        // NEW PHASE 3: Chart Data (Total Comments last 7 days for Admin)
        $stmtChart = $db->query("
            SELECT date(created_at) as activity_date, COUNT(*) as count 
            FROM comments 
            WHERE created_at >= date('now', '-7 days')
            GROUP BY date(created_at)
            ORDER BY activity_date ASC
        ");
        $chartResults = $stmtChart->fetchAll();

        $chartLabels = [];
        $chartData = [];
        
        // Pre-fill the last 7 days with 0 so the chart always looks full
        for ($i = 6; $i >= 0; $i--) {
            $dateString = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('M d', strtotime($dateString));
            
            $count = 0;
            foreach ($chartResults as $row) {
                if ($row['activity_date'] === $dateString) {
                    $count = (int)$row['count'];
                    break;
                }
            }
            $chartData[] = $count;
        }
        // ---------------------------------------------------------

        $html = $this->view->render('dashboard', [
            'title' => 'Secure CMS | Admin Dashboard',
            'stats' => $stats,
            'posts' => $posts,
            'recent_comments' => $recent_comments,
            'recent_logs' => $recent_logs,
            'isAdmin' => $isAdmin,
            'isSubAdmin' => $isSubAdmin,
            'perms' => $perms,
            'chartLabels' => json_encode($chartLabels), // Passing to view!
            'chartData' => json_encode($chartData)      // Passing to view!
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