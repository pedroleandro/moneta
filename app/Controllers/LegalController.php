<?php

namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    public function show(): void
    {
        echo $this->view->render("legal/terms", [
            "title" => "Termos de Uso e Política de Privacidade | " . APP_NAME,
        ]);
    }
}