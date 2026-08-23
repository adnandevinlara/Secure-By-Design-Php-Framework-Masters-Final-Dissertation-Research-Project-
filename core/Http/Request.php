<?php

namespace Core\Http;

class Request
{
    private array $get;
    private array $post;
    private array $server;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // SECURE: Parse the URL to extract ONLY the path, ignoring query parameters
        // This ensures /post/view?id=1 is routed simply as /post/view
        return parse_url($uri, PHP_URL_PATH);
    }

    // Safely retrieve a value from $_POST or $_GET
    public function input(string $key, $default = null)
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        return $default;
    }

    // Retrieve all incoming data
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }
}