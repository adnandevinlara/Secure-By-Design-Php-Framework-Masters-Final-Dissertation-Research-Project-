<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class DashboardController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        // We moved the rendering logic here, out of the router
        $html = $this->view->render('dashboard', [
            'username' => 'Adnan Admin',
            'role' => 'Administrator',
            // Let's change the message slightly so we know the Controller is working
            'maliciousInputTest' => '<script>alert("XSS Neutralized via the new Controller!");</script>'
        ]);

        $res->html($html);
    }
}