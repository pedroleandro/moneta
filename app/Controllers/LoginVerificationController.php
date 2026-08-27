<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\LoginSecurity;
use App\Core\Message;
use JetBrains\PhpStorm\NoReturn;

class LoginVerificationController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    public function show(?array $data): void
    {
        $token = $data["token"] ?? "";
        $verification = LoginSecurity::findToken($token);

        if (!$verification) {
            Message::error("Esse link é inválido ou já expirou.");
            redirect("/entrar");
            return;
        }

        echo $this->view->render("security/verify_login", [
            "title" => "Verificar acesso | " . APP_NAME,
            "token" => $token,
            "verification" => $verification,
        ]);
    }

    #[NoReturn]
    public function confirm(?array $data): void
    {
        $token = $data["token"] ?? "";

        if (LoginSecurity::confirmLegit($token)) {
            Message::success("Obrigado por confirmar! Tudo certo por aqui.");
        } else {
            Message::error("Esse link é inválido ou já expirou.");
        }

        redirect("/entrar");
    }

    #[NoReturn]
    public function reportSuspicious(?array $data): void
    {
        $token = $data["token"] ?? "";
        $resetToken = LoginSecurity::reportSuspicious($token);

        if (!$resetToken) {
            Message::error("Esse link é inválido ou já expirou.");
            redirect("/entrar");
            return;
        }

        Message::warning(
            "Todas as sessões foram encerradas por segurança. Defina uma nova senha para continuar."
        );
        redirect("/resetar-senha/{$resetToken}");
    }
}