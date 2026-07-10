<?php

namespace App\Models;

use Core\Database\Model;

class SecurityLog extends Model
{
    protected string $table = 'security_logs';

    public function logEvent(string $eventType, string $severity, string $ipAddress, string $requestUrl, string $description, ?int $userId = null): bool
    {
        return $this->insert([
            'event_type'  => $eventType,
            'severity'    => $severity,
            'user_id'     => $userId,
            'ip_address'  => $ipAddress,
            'request_url' => $requestUrl,
            'description' => $description,
            'timestamp'   => date('Y-m-d H:i:s')
        ]);
    }
}