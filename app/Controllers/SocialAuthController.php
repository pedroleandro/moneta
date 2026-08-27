<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\LoginSecurity;
use App\Core\Message;
use App\Core\Session;
use App\Core\SessionTimeoutMiddleware;
use App\Core\Turnstile;
use App\Models\SocialAccount;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;
use League\OAuth2\Client\Provider\Facebook;
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

    private function facebookProvider(): Facebook
    {
        return new Facebook([
            "clientId" => FACEBOOK_CLIENT_ID,
            "clientSecret" => FACEBOOK_CLIENT_SECRET,
            "redirectUri" => url("/entrar/facebook/callback"),
            "graphApiVersion" => "v20.0",
        ]);
    }

    // =========================================================
    // GOOGLE
    // =========================================================

    #[NoReturn]
    public function redirectToGoogle(?array $data): void
    {
        $this->validateCsrfToken($data, "/entrar");

        if (!Turnstile::verify($data["cf-turnstile-response"] ?? null, $_SERVER["REMOTE_ADDR"] ?? null)) {
            Message::error("Não foi possível confirmar que você não é um robô. Tente novamente.");
            redirect("/entrar");
            return;
        }

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
                Logger::warning("E-mail do Google não verificado", ["email" => $email]);
                Message::error("Seu e-mail do Google ainda não está verificado. Verifique-o e tente novamente.");
                redirect("/entrar");
                return;
            }

            $this->loginOrRegister("google", $providerId, $email, $name, $avatar, $token->getToken(), $token->getRefreshToken());

        } catch (\Throwable $exception) {
            Logger::error("Falha na autenticação com Google", [
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível entrar com o Google. Tente novamente.");
            redirect("/entrar");
        }
    }

    // =========================================================
    // FACEBOOK
    // =========================================================

    #[NoReturn]
    public function redirectToFacebook(?array $data): void
    {
        $this->validateCsrfToken($data, "/entrar");

        if (!Turnstile::verify($data["cf-turnstile-response"] ?? null, $_SERVER["REMOTE_ADDR"] ?? null)) {
            Message::error("Não foi possível confirmar que você não é um robô. Tente novamente.");
            redirect("/entrar");
            return;
        }

        $provider = $this->facebookProvider();
        $authUrl = $provider->getAuthorizationUrl([
            "scope" => ["email"],
            "auth_type" => "rerequest", // força pedir de novo o e-mail se a pessoa negou antes
        ]);

        $session = new Session();
        $session->set("oauth2state", $provider->getState());

        header("Location: " . $authUrl);
        exit;
    }

    public function handleFacebookCallback(?array $data): void
    {
        $session = new Session();
        $state = $_GET["state"] ?? $data["state"] ?? null;
        $code = $_GET["code"] ?? $data["code"] ?? null;

        if (!$state || $state !== $session->get("oauth2state")) {
            Logger::warning("State do Facebook não confere", [
                "recebido" => $state,
                "esperado" => $session->get("oauth2state"),
            ]);
            Message::error("Sessão de login inválida. Tente novamente.");
            redirect("/entrar");
            return;
        }

        $session->unset("oauth2state");

        $provider = $this->facebookProvider();

        try {
            $token = $provider->getAccessToken("authorization_code", [
                "code" => $code,
            ]);

            $facebookUser = $provider->getResourceOwner($token);
            $facebookData = $facebookUser->toArray();

            $providerId = (string)$facebookUser->getId();
            $email = $facebookData["email"] ?? null;
            $name = $facebookData["name"] ?? "Usuário Facebook";
            $avatar = $facebookUser->getPictureUrl();

            if (!$email) {
                Message::error(
                    "Não foi possível obter um e-mail verificado da sua conta Facebook. " .
                    "Verifique se seu e-mail está confirmado no Facebook e tente novamente."
                );
                redirect("/entrar");
                return;
            }

            $this->loginOrRegister("facebook", $providerId, $email, $name, $avatar, $token->getToken(), $token->getRefreshToken());

        } catch (\Throwable $exception) {
            Logger::error("Falha na autenticação com Facebook", [
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível entrar com o Facebook. Tente novamente.");
            redirect("/entrar");
        }
    }

    // =========================================================
    // FLUXO COMUM (Google + Facebook)
    // =========================================================

    private function loginOrRegister(
        string $provider,
        string $providerId,
        string $email,
        string $name,
        ?string $avatar,
        ?string $accessToken,
        ?string $refreshToken
    ): void {
        $socialAccount = SocialAccount::findByProvider($provider, $providerId);

        if ($socialAccount) {
            $user = (new User())->find($socialAccount->getUserId());

            if (!$user) {
                Message::error("Essa conta não existe mais. Faça um novo cadastro.");
                redirect("/entrar");
                return;
            }

            $this->finishLogin($user, $provider);
            return;
        }

        $user = User::findByEmail($email);

        if ($user) {
            Message::error("Já existe uma conta com esse e-mail. Entre com sua senha para acessá-la.");
            redirect("/entrar");
            return;
        }

        $session = new Session();
        $session->set("pending_social_signup", [
            "provider" => $provider,
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

        LoginSecurity::checkAndNotify(
            $user->getId(),
            $user->getEmail(),
            $user->getName(),
            $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0",
            $_SERVER["HTTP_USER_AGENT"] ?? ""
        );

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
                "provider" => $pending->provider ?? null,
                "exception" => $exception->getMessage(),
            ]);

            $session->unset("pending_social_signup");

            Message::error("Não foi possível criar sua conta. Tente entrar novamente.");
            redirect("/entrar");
        }
    }
}