<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Session;

class DashboardController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        // Set a test variable in the secure session
        Session::set('test_role', 'Super Administrator');
        
        $html = $this->view->render('dashboard', [
            'username' => 'Adnan Admin',
            // Retrieve the variable from the session to prove it works
            'role' => Session::get('test_role', 'Guest'),
            'maliciousInputTest' => '<script>alert("XSS Neutralized!");</script>'
        ]);

        $res->html($html);
    }
}