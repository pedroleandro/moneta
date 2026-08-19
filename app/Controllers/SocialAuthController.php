<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Core\Session;
use App\Core\SessionTimeoutMiddleware;
use App\Models\SocialAccount;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;
use League\OAuth2\Client\Provider\Google;

class SocialAuthController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    private function googleProvider(): Google
    {
        return new Google([
            "clientId" => GOOGLE_CLIENT_ID,
            "clientSecret" => GOOGLE_CLIENT_SECRET,
            "redirectUri" => url("/entrar/google/callback"),
        ]);
    }

    #[NoReturn]
    public function redirectToGoogle(): void
    {
        $provider = $this->googleProvider();
        $authUrl = $provider->getAuthorizationUrl([
            "scope" => ["email", "profile"],
            "prompt" => "select_account consent",
        ]);

        $session = new Session();
        $session->set("oauth2state", $provider->getState());

        header("Location: " . $authUrl);
        exit;
    }

    public function handleGoogleCallback(?array $data): void
    {
        $session = new Session();
        $state = $_GET["state"] ?? $data["state"] ?? null;
        $code = $_GET["code"] ?? $data["code"] ?? null;

        if (!$state || $state !== $session->get("oauth2state")) {
            Logger::warning("State do Google não confere", [
                "recebido" => $state,
                "esperado" => $session->get("oauth2state"),
            ]);
            Message::error("Sessão de login inválida. Tente novamente.");
            redirect("/entrar");
            return;
        }

        $session->unset("oauth2state");

        $provider = $this->googleProvider();

        try {
            $token = $provider->getAccessToken("authorization_code", [
                "code" => $code,
            ]);

            $googleUser = $provider->getResourceOwner($token);
            $googleData = $googleUser->toArray();

            Logger::info("Dados recebidos do Google", ["raw" => $googleData]);

            $providerId = (string)$googleUser->getId();
            $email = $googleData["email"] ?? null;
            $emailVerified = $googleData["email_verified"] ?? $googleData["verified_email"] ?? false;
            $name = $googleData["name"] ?? ($email ?: "Usuário Google");
            $avatar = $googleData["picture"] ?? null;

            if (!$email) {
                Message::error("Não foi possível obter o e-mail da sua conta Google.");
                redirect("/entrar");
                return;
            }

            if (!$emailVerified) {
                Logger::warning("E-mail do Google não verificado", ["email" => $email, "raw" => $googleData]);
                Message::error("Seu e-mail do Google ainda não está verificado. Verifique-o e tente novamente.");
                redirect("/entrar");
                return;
            }

            $this->loginOrRegister($providerId, $email, $name, $avatar, $token->getToken(), $token->getRefreshToken());

        } catch (\Throwable $exception) {
            Logger::error("Falha na autenticação com Google", [
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível entrar com o Google. Tente novamente.");
            redirect("/entrar");
        }
    }

    private function loginOrRegister(
        string  $providerId,
        string  $email,
        string  $name,
        ?string $avatar,
        ?string $accessToken,
        ?string $refreshToken
    ): void
    {
        $socialAccount = SocialAccount::findByProvider("google", $providerId);

        if ($socialAccount) {
            $user = (new User())->find($socialAccount->getUserId());

            if (!$user) {
                Message::error("Essa conta não existe mais. Faça um novo cadastro.");
                redirect("/entrar");
                return;
            }

            $this->finishLogin($user, "google");
            return;
        }

        $user = User::findByEmail($email);

        if ($user) {
            // Já existe conta tradicional com esse e-mail: não vincula
            // automaticamente. A pessoa precisa entrar com a senha e
            // vincular o Google manualmente no perfil (mais seguro).
            Message::error("Já existe uma conta com esse e-mail. Entre com sua senha para acessá-la.");
            redirect("/entrar");
            return;
        }

        $session = new Session();
        $session->set("pending_social_signup", [
            "provider" => "google",
            "provider_id" => $providerId,
            "email" => $email,
            "name" => $name,
            "avatar" => $avatar,
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken,
        ]);

        redirect("/cadastrar/confirmar-social");
    }

    private function linkSocialAccount(User $user, string $provider, string $providerId, ?string $accessToken, ?string $refreshToken): void
    {
        $socialAccount = new SocialAccount();
        $socialAccount->setUserId($user->getId());
        $socialAccount->setProvider($provider);
        $socialAccount->setProviderId($providerId);
        $socialAccount->setAccessToken($accessToken);
        $socialAccount->setRefreshToken($refreshToken);
        $socialAccount->save();

        AuditLog::record(LogEvent::SOCIAL_ACCOUNT_LINKED, $user->getId(), ["provider" => $provider]);
    }

    private function finishLogin(User $user, string $provider): void
    {
        $session = new Session();
        $session->regenerate();
        $session->set("auth", $user->toSessionData());

        SessionTimeoutMiddleware::start();

        AuditLog::record(LogEvent::LOGIN_SUCCESS, $user->getId(), ["via" => $provider]);

        redirect("/dashboard");
    }

    public function confirmSocialSignup(): void
    {
        $session = new Session();
        $pending = $session->get("pending_social_signup");

        if (!$pending) {
            Message::error("Nada pendente para confirmar. Tente entrar novamente.");
            redirect("/entrar");
            return;
        }

        echo $this->view->render("auth/auth_confirm_social", [
            "title" => "Confirmar cadastro | " . APP_NAME,
            "pending" => $pending,
        ]);
    }

    public function storeSocialSignup(?array $data): void
    {
        $this->validateCsrfToken($data, "/cadastrar/confirmar-social");

        $session = new Session();
        $pending = $session->get("pending_social_signup");

        if (!$pending) {
            Message::error("Nada pendente para confirmar. Tente entrar novamente.");
            redirect("/entrar");
            return;
        }

        if (empty($data["terms"] ?? null)) {
            Message::error("Você precisa aceitar os termos de uso.");
            redirect("/cadastrar/confirmar-social");
            return;
        }

        try {
            $user = new User();
            $user->fill([
                "name" => $pending->name,
                "email" => $pending->email,
                "password" => null,
                "avatar" => $pending->avatar,
            ]);
            $user->markEmailAsVerified();
            $user->save();

            AuditLog::record(LogEvent::USER_REGISTERED, $user->getId(), ["via" => $pending->provider]);

            $this->linkSocialAccount(
                $user,
                $pending->provider,
                $pending->provider_id,
                $pending->access_token,
                $pending->refresh_token
            );

            $session->unset("pending_social_signup");

            $this->finishLogin($user, $pending->provider);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar conta via login social", [
                "email" => $pending->email ?? null,
                "exception" => $exception->getMessage(),
            ]);

            $session->unset("pending_social_signup");

            Message::error("Não foi possível criar sua conta. Tente entrar com o Google novamente.");
            redirect("/entrar");
        }
    }
}