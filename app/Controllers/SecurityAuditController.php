<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class SecurityAuditController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        // Fetch filter parameters securely
        $filterType = htmlspecialchars($_GET['type'] ?? '');
        $filterSeverity = htmlspecialchars($_GET['severity'] ?? '');
        $filterDate = htmlspecialchars($_GET['date'] ?? '');
        $filterUser = htmlspecialchars($_GET['user'] ?? '');

        $logFilePath = __DIR__ . '/../../logs/security.log';
        $logs = [];

        if (file_exists($logFilePath)) {
            $rawLogs = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $rawLogs = array_reverse($rawLogs); // Show newest first
            
            foreach ($rawLogs as $line) {
                $logEntry = [
                    'time' => '', 'severity' => '', 'event' => '', 'user' => '', 'ip' => '', 'url' => ''
                ];

                // Check if this is a simple Dummy Log
                if (preg_match('/^\[(.*?)\]\s+\[(.*?)\]\s+\[(.*?)\]\s+\[(.*?)\]\s+\[(.*?)\]\s+\[(.*?)\]/', $line, $matches) && strpos($line, 'IP:') === false) {
                    $logEntry['time']     = $matches[1];
                    $logEntry['severity'] = $matches[2];
                    $logEntry['event']    = $matches[3];
                    $logEntry['user']     = $matches[4];
                    $logEntry['ip']       = $matches[5];
                    $logEntry['url']      = $matches[6];
                } else {
                    // Dynamically parse Real Framework Logs
                    preg_match('/^\[(.*?)\]/', $line, $timeMatch);
                    $logEntry['time'] = $timeMatch[1] ?? 'Unknown Time';

                    // Determine Severity
                    if (preg_match('/\[(CRITICAL|ALERT|High)\]/i', $line)) $logEntry['severity'] = 'High';
                    elseif (preg_match('/\[(WARNING|Medium)\]/i', $line)) $logEntry['severity'] = 'Medium';
                    else $logEntry['severity'] = 'Low'; // Default for violations missing explicit severity

                    // Extract Event Type
                    preg_match('/\[([A-Z_]+_VIOLATION|[A-Z_]+_FAILED|[A-Z_]+_BLOCKED|[A-Z_]+_LOCKOUT)\]/', $line, $evtMatch);
                    $logEntry['event'] = $evtMatch[1] ?? 'SECURITY_EVENT';

                    // Extract IP Address
                    preg_match('/\[IP:\s*(.*?)\]/', $line, $ipMatch);
                    $logEntry['ip'] = $ipMatch[1] ?? 'Unknown';

                    // Extract User or UID
                    preg_match('/\[(?:UID|User):\s*(.*?)\]/', $line, $usrMatch);
                    $logEntry['user'] = $usrMatch[1] ?? 'Unknown';

                    // Extract URL if present
                    preg_match('/\[(?:GET|POST)\s+(.*?)\]/', $line, $urlMatch);
                    $logEntry['url'] = $urlMatch[1] ?? '-';
                }

                // Apply Filters
                if ($filterType && stripos($logEntry['event'], $filterType) === false) continue;
                if ($filterSeverity && stripos($logEntry['severity'], $filterSeverity) === false) continue;
                if ($filterDate && strpos($logEntry['time'], $filterDate) === false) continue;
                if ($filterUser && stripos($logEntry['user'], $filterUser) === false) continue;

                $logs[] = $logEntry;
            }
        }

        $html = $this->view->render('security_logs', [
            'title' => 'Secure CMS | Security Logs',
            'logs' => $logs,
            'filters' => [
                'type' => $filterType,
                'severity' => $filterSeverity,
                'date' => $filterDate,
                'user' => $filterUser
            ]
        ]);

        $res->html($html);
    }

    /**
     * Active Defense: Appends an IP address to the firewall blocklist
     */
    public function blockIp(Request $req, Response $res): void
    {
        $ipToBlock = htmlspecialchars($_POST['ip_address'] ?? '');
        
        if (!empty($ipToBlock)) {
            $blockedFile = __DIR__ . '/../../logs/blocked_ips.json';
            $blockedIps = [];
            
            // Read existing blocked IPs
            if (file_exists($blockedFile)) {
                $blockedIps = json_decode(file_get_contents($blockedFile), true) ?? [];
            }
            
            // Add new IP if it isn't already blocked
            if (!in_array($ipToBlock, $blockedIps)) {
                $blockedIps[] = $ipToBlock;
                file_put_contents($blockedFile, json_encode($blockedIps, JSON_PRETTY_PRINT));
            }
        }
        
        // Redirect back to the Audit logs to see the change
        header("Location: /security-logs");
        exit;
    }
}