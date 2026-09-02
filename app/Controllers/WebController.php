<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;

class WebController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    #[NoReturn]
    public function index(): void
    {
        redirect("/entrar");
        echo $this->view->render("home", [
            "title" => "Home | " . APP_NAME
        ]);
    }
}