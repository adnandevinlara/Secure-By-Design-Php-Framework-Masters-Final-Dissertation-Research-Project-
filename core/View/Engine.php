<?php

namespace Core\View;

class Engine
{
    public function render(string $viewPath, array $data = []): string
    {
        // Make the data array variables available to the included file
        extract($data);
        
        // Start output buffering so we capture the HTML instead of printing it directly
        ob_start();
        
        // Include the view file from the app/Views directory
        require __DIR__ . '/../../app/Views/' . $viewPath . '.php';
        
        return ob_get_clean();
    }

    public function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function escapeJs(string $string): string
    {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}