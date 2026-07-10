<?php

namespace Core\Http;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    // Used for rendering HTML templates later
    public function html(string $content, int $status = 200): void
    {
        $this->setStatusCode($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
    }

    // Used for API endpoints
    public function json(array $data, int $status = 200): void
    {
        $this->setStatusCode($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
    }
}