<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\DatabaseSessionHandler;
use App\Core\Email;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Core\PasswordPolicy;
use App\Core\Session;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireLogin();
    }

    public function index(): void
    {
        try {
            $userId = Auth::user()->id;

            $user = User::find($userId);
            $profile = UserProfile::findOrCreateForUser($userId);
            $socialAccounts = SocialAccount::findAllForUser($userId);
            $sessions = DatabaseSessionHandler::listForUser($userId);
            $currentSessionId = session_id();

            echo $this->view->render("profile/index", [
                "title" => "Meu Perfil | " . APP_NAME,
                "active" => "perfil",
                "user" => $user,
                "profile" => $profile,
                "socialAccounts" => $socialAccounts,
                "sessions" => $sessions,
                "currentSessionId" => $currentSessionId,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar perfil", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seu perfil.");
            redirect("/dashboard");
        }
    }

    public function updatePersonalData(?array $data): void
    {
        $this->validateCsrfToken($data, "/perfil");

        $userId = Auth::user()->id;

        try {
            $user = User::find($userId);

            if (!$user) {
                Message::error("Usuário não encontrado.");
                redirect("/perfil");
                return;
            }

            $newEmail = trim($data["email"] ?? "");
            $emailChanged = $newEmail !== $user->getEmail();

            $user->setName(trim($data["name"] ?? ""));
            $user->setEmail($newEmail);

            if ($emailChanged) {
                if ((new User())->existsByEmail($newEmail, $userId)) {
                    Message::error("Já existe uma conta com esse e-mail.");
                    redirect("/perfil");
                    return;
                }

                $token = $user->setEmailVerificationToken();
                $user->save();

                try {
                    $verifyUrl = url("/verificar-email/" . $token);
                    (new Email())->bootstrap(
                        "Confirme seu novo e-mail | " . APP_NAME,
                        "Você alterou seu e-mail no Moneta. Clique para confirmar: <a href=\"{$verifyUrl}\">{$verifyUrl}</a>.",
                        $user->getEmail(),
                        $user->getName()
                    )->send();
                } catch (\Throwable $exception) {
                    Logger::error("Falha ao enviar verificação de novo e-mail", [
                        "user_id" => $userId,
                        "exception" => $exception->getMessage(),
                    ]);
                }

                Message::warning("E-mail alterado. Confirme o novo e-mail para continuar usando-o em logins futuros.");
            } else {
                $user->save();
            }

            $profile = UserProfile::findOrCreateForUser($userId);
            $profile->fill([
                "cpf" => $data["cpf"] ?? null,
                "phone" => $data["phone"] ?? null,
                "birth_date" => $data["birth_date"] ?? null,
                "gender" => $data["gender"] ?? null,
                "zip_code" => $data["zip_code"] ?? null,
                "address" => $data["address"] ?? null,
                "address_number" => $data["address_number"] ?? null,
                "neighborhood" => $data["neighborhood"] ?? null,
                "city" => $data["city"] ?? null,
                "state" => $data["state"] ?? null,
                "bio" => $data["bio"] ?? null,
            ]);
            $profile->save();

            $session = new Session();
            $session->set("auth", $user->toSessionData());

            AuditLog::record(LogEvent::PROFILE_UPDATED, $userId);

            if (!$emailChanged) {
                Message::success("Dados atualizados com sucesso.");
            }

            redirect("/perfil");
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/perfil");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar dados pessoais", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível salvar seus dados. Tente novamente.");
            redirect("/perfil");
        }
    }

    public function updatePassword(?array $data): void
    {
        $this->validateCsrfToken($data, "/perfil");

        $userId = Auth::user()->id;

        try {
            $user = User::find($userId);

            if (!$user) {
                Message::error("Usuário não encontrado.");
                redirect("/perfil");
                return;
            }

            $currentPassword = $data["current_password"] ?? "";
            $newPassword = $data["password"] ?? "";
            $passwordConfirm = $data["password-confirm"] ?? "";

            if ($user->hasPassword() && !$user->passwordVerify($currentPassword)) {
                Message::error("Senha atual incorreta.");
                redirect("/perfil");
                return;
            }

            if ($newPassword !== $passwordConfirm) {
                Message::error("As senhas não conferem.");
                redirect("/perfil");
                return;
            }

            if (PasswordPolicy::isPwned($newPassword) === true) {
                Message::error("Essa senha já apareceu em vazamentos conhecidos. Escolha outra senha.");
                redirect("/perfil");
                return;
            }

            $wasFirstPassword = !$user->hasPassword();

            $user->setPassword($newPassword);
            $user->save();

            AuditLog::record(LogEvent::PASSWORD_CHANGED, $userId);
            DatabaseSessionHandler::destroyAllForUser($userId);

            $session = new Session();
            $session->regenerate();
            $session->set("auth", $user->toSessionData());

            Message::success($wasFirstPassword ? "Senha criada com sucesso." : "Senha alterada com sucesso.");
            redirect("/perfil");
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/perfil");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao trocar senha pelo perfil", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível trocar a senha. Tente novamente.");
            redirect("/perfil");
        }
    }

    public function unlinkSocialAccount(?array $data): void
    {
        $this->validateCsrfToken($data, "/perfil");

        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $socialAccount = SocialAccount::findByIdForUser($id, $userId);

            if (!$socialAccount) {
                Message::error("Vínculo não encontrado.");
                redirect("/perfil");
                return;
            }

            $user = User::find($userId);

            if (!$user->hasPassword() && count(SocialAccount::findAllForUser($userId)) <= 1) {
                Message::error(
                    "Você não pode desvincular essa conta porque é a única forma de login que você tem. " .
                    "Crie uma senha antes de desvincular."
                );
                redirect("/perfil");
                return;
            }

            $socialAccount->delete();

            AuditLog::record(LogEvent::SOCIAL_ACCOUNT_UNLINKED, $userId, [
                "provider" => $socialAccount->getProvider(),
            ]);

            Message::success("Conta desvinculada com sucesso.");
            redirect("/perfil");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao desvincular conta social", [
                "user_id" => $userId,
                "social_account_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível desvincular a conta. Tente novamente.");
            redirect("/perfil");
        }
    }

    public function destroySession(?array $data): void
    {
        $this->validateCsrfToken($data, "/perfil");

        $userId = Auth::user()->id;
        $sessionId = $data["id"] ?? "";

        try {
            $sessions = DatabaseSessionHandler::listForUser($userId);
            $belongsToUser = false;

            foreach ($sessions as $session) {
                if ($session->id === $sessionId) {
                    $belongsToUser = true;
                    break;
                }
            }

            if (!$belongsToUser) {
                Message::error("Sessão não encontrada.");
                redirect("/perfil");
                return;
            }

            if ($sessionId === session_id()) {
                Message::error("Você não pode encerrar a sessão que está usando agora. Use \"Sair\" para isso.");
                redirect("/perfil");
                return;
            }

            $handler = new DatabaseSessionHandler();
            $handler->destroy($sessionId);

            AuditLog::record(LogEvent::SESSION_REVOKED, $userId);

            Message::success("Sessão encerrada com sucesso.");
            redirect("/perfil");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao encerrar sessão", [
                "user_id" => $userId,
                "session_id" => $sessionId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível encerrar a sessão. Tente novamente.");
            redirect("/perfil");
        }
    }

    public function destroyOtherSessions(?array $data): void
    {
        $this->validateCsrfToken($data, "/perfil");

        $userId = Auth::user()->id;
        $currentSessionId = session_id();

        try {
            $sessions = DatabaseSessionHandler::listForUser($userId);
            $handler = new DatabaseSessionHandler();

            foreach ($sessions as $session) {
                if ($session->id !== $currentSessionId) {
                    $handler->destroy($session->id);
                }
            }

            AuditLog::record(LogEvent::SESSION_REVOKED, $userId, ["scope" => "all_others"]);

            Message::success("Todas as outras sessões foram encerradas.");
            redirect("/perfil");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao encerrar outras sessões", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível encerrar as sessões. Tente novamente.");
            redirect("/perfil");
        }
    }
}