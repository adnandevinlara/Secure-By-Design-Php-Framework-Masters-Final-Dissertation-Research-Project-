<?php

namespace Core\Security;

class Logger
{
    // Store logs securely outside the public directory
    private static string $logPath = __DIR__ . '/../../logs/security.log';

    public static function log(string $severity, string $eventType, string $message): void
    {
        $timestamp = date('c'); // ISO 8601 format[cite: 1]
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
        $userId = \Core\Http\Session::get('user_id', 'Guest');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $url = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
        
        // Format: [Timestamp] [Severity] [Event] [IP] [User ID] [Method URL] - Message
        $logEntry = sprintf(
            "[%s] [%s] [%s] [IP: %s] [UID: %s] [%s %s] - %s" . PHP_EOL,
            $timestamp,
            strtoupper($severity),
            strtoupper($eventType),
            $ipAddress,
            $userId,
            $method,
            $url,
            $message
        );

        self::write($logEntry);
    }

    /**
     * Safely creates the directory and appends the log.
     */
    private static function write(string $entry): void
    {
        $dir = dirname(self::$logPath);
        
        // Create the logs folder if it doesn't exist yet
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // FILE_APPEND ensures we add to the end of the file
        // LOCK_EX prevents file corruption if multiple requests hit at the exact same millisecond
        file_put_contents(self::$logPath, $entry, FILE_APPEND | LOCK_EX);
    }
}