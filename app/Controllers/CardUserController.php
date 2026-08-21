<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\CardUser;
use App\Models\CreditCard;

class CardUserController extends Controller
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
            $cardUsers = CardUser::findAllForUser($userId);

            echo $this->view->render("people_card/index", [
                "title" => "Quem Usa Meu Cartão | " . APP_NAME,
                "active" => "pessoas-cartao",
                "cardUsers" => $cardUsers,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar pessoas do cartão", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a lista.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        try {
            $userId = Auth::user()->id;
            $cards = CreditCard::findAllForUser($userId);

            if (empty($cards)) {
                Message::warning("Cadastre um cartão de crédito antes de adicionar pessoas a ele.");
                redirect("/cartoes/novo");
                return;
            }

            echo $this->view->render("people_card/create", [
                "title" => "Nova Pessoa | " . APP_NAME,
                "active" => "pessoas-cartao",
                "cards" => $cards,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar formulário de nova pessoa no cartão", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o formulário.");
            redirect("/pessoas-cartao");
        }
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/pessoas-cartao/nova");

        $userId = Auth::user()->id;

        try {
            $selectedCardIds = $this->validateSelectedCards($data, $userId);

            if (is_string($selectedCardIds)) {
                flash_old($data);
                Message::error($selectedCardIds);
                redirect("/pessoas-cartao/nova");
                return;
            }

            $cardUser = new CardUser();
            $cardUser->fill([
                "owner_user_id" => $userId,
                "name" => trim($data["name"] ?? ""),
                "phone" => $data["phone"] ?? null,
                "notes" => $data["notes"] ?? null,
            ]);

            $errors = $cardUser->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/pessoas-cartao/nova");
                return;
            }

            $cardUser->save();
            $cardUser->syncCards($selectedCardIds);

            AuditLog::record(LogEvent::CARD_USER_CREATED, $userId, [
                "card_user_id" => $cardUser->getId(),
                "name" => $cardUser->getName(),
                "credit_card_ids" => $selectedCardIds,
            ]);

            clear_old();

            Message::success("Pessoa adicionada com sucesso.");
            redirect("/pessoas-cartao");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/pessoas-cartao/nova");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao adicionar pessoa ao cartão", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível adicionar a pessoa. Tente novamente.");
            redirect("/pessoas-cartao/nova");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $cardUser = CardUser::findByIdForUser($id, $userId);

            if (!$cardUser) {
                Message::error("Registro não encontrado.");
                redirect("/pessoas-cartao");
                return;
            }

            $cards = CreditCard::findAllForUser($userId);

            echo $this->view->render("people_card/edit", [
                "title" => "Editar Pessoa | " . APP_NAME,
                "active" => "pessoas-cartao",
                "cardUser" => $cardUser,
                "cards" => $cards,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar pessoa do cartão para edição", [
                "user_id" => $userId,
                "card_user_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o registro.");
            redirect("/pessoas-cartao");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/pessoas-cartao/{$id}/editar");

        try {
            $cardUser = CardUser::findByIdForUser($id, $userId);

            if (!$cardUser) {
                Message::error("Registro não encontrado.");
                redirect("/pessoas-cartao");
                return;
            }

            $selectedCardIds = $this->validateSelectedCards($data, $userId);

            if (is_string($selectedCardIds)) {
                flash_old($data);
                Message::error($selectedCardIds);
                redirect("/pessoas-cartao/{$id}/editar");
                return;
            }

            $cardUser->fill([
                "name" => trim($data["name"] ?? ""),
                "phone" => $data["phone"] ?? null,
                "notes" => $data["notes"] ?? null,
            ]);

            $errors = $cardUser->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/pessoas-cartao/{$id}/editar");
                return;
            }

            $cardUser->save();
            $cardUser->syncCards($selectedCardIds);

            AuditLog::record(LogEvent::CARD_USER_UPDATED, $userId, [
                "card_user_id" => $cardUser->getId(),
                "name" => $cardUser->getName(),
            ]);

            clear_old();

            Message::success("Registro atualizado com sucesso.");
            redirect("/pessoas-cartao");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/pessoas-cartao/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar pessoa do cartão", [
                "user_id" => $userId,
                "card_user_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar o registro. Tente novamente.");
            redirect("/pessoas-cartao/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/pessoas-cartao");

        try {
            $cardUser = CardUser::findByIdForUser($id, $userId);

            if (!$cardUser) {
                Message::error("Registro não encontrado.");
                redirect("/pessoas-cartao");
                return;
            }

            if (!$cardUser->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir esse registro.");
                redirect("/pessoas-cartao");
                return;
            }

            if ($cardUser->isInUse()) {
                Message::error(
                    "Esse registro não pode ser excluído porque já está vinculado a lançamentos " .
                    "ou compras parceladas."
                );
                redirect("/pessoas-cartao");
                return;
            }

            $cardUser->delete();

            AuditLog::record(LogEvent::CARD_USER_DELETED, $userId, [
                "card_user_id" => $id,
                "name" => $cardUser->getName(),
            ]);

            Message::success("Registro excluído com sucesso.");
            redirect("/pessoas-cartao");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir pessoa do cartão", [
                "user_id" => $userId,
                "card_user_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir o registro. Tente novamente.");
            redirect("/pessoas-cartao");
        }
    }

    private function validateSelectedCards(array $data, int $userId): array|string
    {
        $submittedIds = $data["credit_card_ids"] ?? [];

        if (empty($submittedIds)) {
            return [];
        }

        $validIds = [];

        foreach ($submittedIds as $cardId) {
            $card = CreditCard::findByIdForUser((int)$cardId, $userId);

            if (!$card) {
                return "Um dos cartões selecionados é inválido.";
            }

            $validIds[] = (int)$cardId;
        }

        return $validIds;
    }
}