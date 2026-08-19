<?php

namespace App\Core;

class SessionTimeoutMiddleware
{
    public const DEFAULT_TIMEOUT = 60 * 60 * 2;
    public const REMEMBER_TIMEOUT = 60 * 60 * 12;

    public static function handle(): void
    {
        $loggedInAt = $_SESSION['logged_in_at'] ?? null;

        if (!$loggedInAt) {
            self::expireSession();
            return;
        }

        $timeout = !empty($_SESSION['remember_me']) ? self::REMEMBER_TIMEOUT : self::DEFAULT_TIMEOUT;
        $sessionDuration = time() - $loggedInAt;

        if ($sessionDuration > $timeout) {
            self::expireSession();
            return;
        }
    }

    public static function start(bool $rememberMe = false): void
    {
        $_SESSION['logged_in_at'] = time();
        $_SESSION['remember_me'] = $rememberMe;
    }

    private static function expireSession(): void
    {
        $session = new Session();
        $session->unset("auth");
        $session->unset("logged_in_at");
        $session->unset("remember_me");

        Message::warning("Sua sessão expirou. Faça login novamente.");
        redirect("/entrar");
        exit;
    }
}