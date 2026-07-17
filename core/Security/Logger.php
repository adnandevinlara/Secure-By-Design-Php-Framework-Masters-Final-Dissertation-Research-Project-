<?php

namespace Core\Security;

class Logger
{
    // Store logs securely outside the public directory
    private static string $logPath = __DIR__ . '/../../logs/security.log';

    /**
     * Writes a security event to the log file.
     */
    public static function log(string $eventType, string $message, string $username = 'Guest'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
        
        // Format: [Date] [Event] [IP] [User] Message
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [User: %s] %s" . PHP_EOL,
            $timestamp,
            strtoupper($eventType),
            $ipAddress,
            $username,
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