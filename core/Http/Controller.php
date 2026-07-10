<?php

namespace Core\Http;

use Core\View\Engine;

abstract class Controller
{
    protected Engine $view;

    public function __construct()
    {
        $this->view = new Engine();
    }
}