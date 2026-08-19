<?php

namespace App\Controllers;

use Core\Http\Controller;
use Core\Http\Request;
use Core\Http\Response;

class ProfileController extends Controller
{
    public function index(Request $req, Response $res): void
    {
        $html = $this->view->render('profile', [
            'title' => 'Secure CMS | My Profile',
            // Pass the active session user data to the view
            'user' => $_SESSION['user'] ?? ['username' => 'Unknown', 'email' => 'N/A', 'role' => 'Guest']
        ]);

        $res->html($html);
    }
}