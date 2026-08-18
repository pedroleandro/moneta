<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Email;
use App\Core\Message;
use App\Core\Session;
use App\Core\SessionTimeoutMiddleware;
use App\Models\LoginAttempt;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;
use App\Core\AuditLog;
use App\Core\LogEvent;
use App\Core\RateLimiter;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    public function index(): void
    {
        if (Auth::check()) {
            redirect("/dashboard");
            return;
        }

        echo $this->view->render("auth/auth_login", [
            "title" => "Entrar | " . APP_NAME
        ]);

        clear_old();
    }

    #[NoReturn]
    public function authenticate(?array $data): void
    {
        $this->validateCsrfToken($data, "/entrar");

        $email = trim($data["email"] ?? "");
        $password = $data["password"] ?? "";
        $ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";

        if (!$email || !$password) {
            flash_old(["email" => $email]);
            Message::error("Informe e-mail e senha.");
            redirect("/entrar");
            return;
        }

        if (RateLimiter::tooManyAttempts($email, $ip)) {
            $minutos = RateLimiter::minutesRemaining($email, $ip);
            AuditLog::record(LogEvent::ACCOUNT_LOCKED, null, ["email" => $email]);
            Message::error("Muitas tentativas de login. Tente novamente em {$minutos} minuto(s).");
            redirect("/entrar");
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !$user->passwordVerify($password)) {
            RateLimiter::hit($email, $ip, false);
            AuditLog::record(LogEvent::LOGIN_FAILED, $user?->getId(), ["email" => $email]);
            flash_old(["email" => $email]);
            Message::error("E-mail ou senha inválidos.");
            redirect("/entrar");
            return;
        }

        RateLimiter::hit($email, $ip, true);

        $session = new Session();
        $session->regenerate();
        $session->set("auth", $user->toSessionData());

        SessionTimeoutMiddleware::start();

        AuditLog::record(LogEvent::LOGIN_SUCCESS, $user->getId());

        clear_old();

        redirect("/dashboard");
    }

    #[NoReturn]
    public function logout(): void
    {
        $user = Auth::user();
        AuditLog::record(LogEvent::LOGOUT, $user->id ?? null);

        Auth::logout();
        redirect("/entrar");
    }

    public function create(): void
    {
        if (Auth::check()) {
            redirect("/dashboard");
            return;
        }

        echo $this->view->render("auth/auth_register", [
            "title" => "Cadastrar | " . APP_NAME
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/cadastrar");

        $name = trim($data["name"] ?? "");
        $email = trim($data["email"] ?? "");
        $password = $data["password"] ?? "";
        $passwordConfirm = $data["password-confirm"] ?? "";
        $terms = $data["terms"] ?? null;

        if(empty($name) || empty($email) || empty($password) || empty($passwordConfirm)) {
            Message::error("Você precisa preencher o formulário.");
            redirect("/cadastrar");
            return;
        }

        if (!$terms) {
            flash_old(["name" => $name, "email" => $email]);
            Message::error("Você precisa aceitar os termos de uso.");
            redirect("/cadastrar");
            return;
        }

        if ($password !== $passwordConfirm) {
            flash_old(["name" => $name, "email" => $email]);
            Message::error("As senhas não conferem.");
            redirect("/cadastrar");
            return;
        }

        $user = new User();

        try {
            $user->fill([
                "name" => $name,
                "email" => $email,
                "password" => $password,
            ]);
        } catch (\InvalidArgumentException $exception) {
            flash_old(["name" => $name, "email" => $email]);
            Message::error($exception->getMessage());
            redirect("/cadastrar");
            return;
        }

        $errors = $user->validate($data);

        if ($errors) {
            flash_old(["name" => $name, "email" => $email]);
            Message::error(implode(" ", $errors));
            redirect("/cadastrar");
            return;
        }

        if ($user->existsByEmail($email)) {
            flash_old(["name" => $name, "email" => $email]);
            Message::error("Já existe uma conta com esse e-mail.");
            redirect("/cadastrar");
            return;
        }

        $user->save();

        AuditLog::record(LogEvent::USER_REGISTERED, $user->getId());

        clear_old();

        redirect("/cadastrar/sucesso");
    }

    public function storeSuccess(): void
    {
        echo $this->view->render("auth/auth_register_success", [
            "title" => "Confirmar cadastro | " . APP_NAME
        ]);
    }

    public function forgotPassword(): void
    {
        echo $this->view->render("auth/auth_forgot_password", [
            "title" => "Esqueceu a senha | " . APP_NAME
        ]);
    }

    /**
     * @throws RandomException
     */
    public function sendResetLink(?array $data): void
    {
        $this->validateCsrfToken($data, "/esqueceu-senha");

        $email = trim($data["email"] ?? "");
        $user = User::findByEmail($email);

        if(empty($email)){
            Message::error("Você precisa informar o e-mail.");
            redirect("/esqueceu-senha");
            return;
        }

        if ($user) {
            $token = $user->setResetToken();
            $user->save();

            AuditLog::record(LogEvent::PASSWORD_RESET_REQUESTED, $user->getId());

            try {
                $resetUrl = url("/resetar-senha/" . $token);

                (new Email())
                    ->bootstrap(
                        "Redefinição de senha | " . APP_NAME,
                        "Clique no link para redefinir sua senha: <a href=\"{$resetUrl}\">{$resetUrl}</a>. O link expira em 2 horas.",
                        $user->getEmail(),
                        $user->getName()
                    )
                    ->send();
            } catch (\Throwable $exception) {
                // Falha de envio não deve expor detalhes ao usuário.
            }
        }

        redirect("/redefinir-senha/sucesso");
    }

    public function sendResetLinkSuccess(): void
    {
        echo $this->view->render("auth/auth_forgot_password_success", [
            "title" => "Redefinir Senha | " . APP_NAME
        ]);
    }

    public function resetPassword(?array $data): void
    {
        $token = $data["token"] ?? "";
        $user = $token ? User::findByResetToken($token) : null;

        if (!$user || $user->resetTokenIsExpired()) {
            Message::error("Link de redefinição inválido ou expirado.");
            redirect("/esqueceu-senha");
            return;
        }

        echo $this->view->render("auth/auth_reset_password", [
            "title" => "Redefinir Senha | " . APP_NAME,
            "token" => $token
        ]);
    }

    /**
     * @throws \Exception
     */
    public function updatePassword(?array $data): void
    {
        $this->validateCsrfToken($data, "/esqueceu-senha");

        $token = $data["token"] ?? "";
        $password = $data["password"] ?? "";
        $passwordConfirm = $data["password-confirm"] ?? "";

        $user = $token ? User::findByResetToken($token) : null;

        if (!$user || $user->resetTokenIsExpired()) {
            Message::error("Link de redefinição inválido ou expirado.");
            redirect("/esqueceu-senha");
            return;
        }

        if ($password !== $passwordConfirm) {
            Message::error("As senhas não conferem.");
            redirect("/resetar-senha/" . $token);
            return;
        }

        try {
            $user->setPassword($password);
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/resetar-senha/" . $token);
            return;
        }

        $user->clearResetToken();
        $user->save();

        AuditLog::record(LogEvent::PASSWORD_CHANGED, $user->getId());

        Auth::logout();

        Message::success("Senha redefinida com sucesso. Faça login.");
        redirect("/entrar");
    }
}