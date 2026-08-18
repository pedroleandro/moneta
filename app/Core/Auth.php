<?php

namespace App\Core;

class Auth
{
    public static function user(): ?object
    {
        $session = new Session();
        return $session->auth ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): void
    {
        $session = new Session();
        $session->unset("auth");
        $session->unset("logged_in_at");
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Message::warning("Você precisa fazer login para continuar.");
            redirect("/entrar");
            return;
        }

        SessionTimeoutMiddleware::handle();
    }
}