<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireLogin();
    }

    public function index(): void
    {
        echo $this->view->render("dashboard/dashboard", [
            "title" => "Dashboard | " . APP_NAME,
            "user" => Auth::user()
        ]);
    }
}