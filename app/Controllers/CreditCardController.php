<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\CreditCard;

class CreditCardController extends Controller
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
            $cards = CreditCard::findAllForUser($userId);

            echo $this->view->render("credit_cards/index", [
                "title" => "Cartões de Crédito | " . APP_NAME,
                "active" => "cartoes",
                "cards" => $cards,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar cartões de crédito", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seus cartões.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        echo $this->view->render("credit_cards/create", [
            "title" => "Novo Cartão | " . APP_NAME,
            "active" => "cartoes",
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/cartoes/novo");

        $userId = Auth::user()->id;

        try {
            $card = new CreditCard();
            $card->fill([
                "user_id" => $userId,
                "name" => trim($data["name"] ?? ""),
                "card_limit" => $data["card_limit"] ?? "0",
                "closing_day" => $data["closing_day"] ?? "",
                "due_day" => $data["due_day"] ?? "",
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
                "is_active" => true,
            ]);

            $errors = $card->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/cartoes/novo");
                return;
            }

            $card->save();

            AuditLog::record(LogEvent::CREDIT_CARD_CREATED, $userId, [
                "card_id" => $card->getId(),
                "name" => $card->getName(),
            ]);

            clear_old();

            Message::success("Cartão criado com sucesso.");
            redirect("/cartoes");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/cartoes/novo");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar cartão de crédito", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar o cartão. Tente novamente.");
            redirect("/cartoes/novo");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $card = CreditCard::findByIdForUser($id, $userId);

            if (!$card) {
                Message::error("Cartão não encontrado.");
                redirect("/cartoes");
                return;
            }

            echo $this->view->render("credit_cards/edit", [
                "title" => "Editar Cartão | " . APP_NAME,
                "active" => "cartoes",
                "card" => $card,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar cartão para edição", [
                "user_id" => $userId,
                "card_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o cartão.");
            redirect("/cartoes");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/cartoes/{$id}/editar");

        try {
            $card = CreditCard::findByIdForUser($id, $userId);

            if (!$card) {
                Message::error("Cartão não encontrado.");
                redirect("/cartoes");
                return;
            }

            $card->fill([
                "name" => trim($data["name"] ?? ""),
                "card_limit" => $data["card_limit"] ?? "0",
                "closing_day" => $data["closing_day"] ?? "",
                "due_day" => $data["due_day"] ?? "",
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
                "is_active" => !empty($data["is_active"]),
            ]);

            $errors = $card->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/cartoes/{$id}/editar");
                return;
            }

            $card->save();

            AuditLog::record(LogEvent::CREDIT_CARD_UPDATED, $userId, [
                "card_id" => $card->getId(),
                "name" => $card->getName(),
            ]);

            clear_old();

            Message::success("Cartão atualizado com sucesso.");
            redirect("/cartoes");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/cartoes/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar cartão de crédito", [
                "user_id" => $userId,
                "card_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar o cartão. Tente novamente.");
            redirect("/cartoes/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/cartoes");

        try {
            $card = CreditCard::findByIdForUser($id, $userId);

            if (!$card) {
                Message::error("Cartão não encontrado.");
                redirect("/cartoes");
                return;
            }

            if (!$card->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir esse cartão.");
                redirect("/cartoes");
                return;
            }

            if ($card->isInUse()) {
                Message::error(
                    "Esse cartão não pode ser excluído porque já está vinculado a lançamentos, faturas, " .
                    "pessoas cadastradas, recorrências ou compras parceladas."
                );
                redirect("/cartoes");
                return;
            }

            $card->delete();

            AuditLog::record(LogEvent::CREDIT_CARD_DELETED, $userId, [
                "card_id" => $id,
                "name" => $card->getName(),
            ]);

            Message::success("Cartão excluído com sucesso.");
            redirect("/cartoes");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir cartão de crédito", [
                "user_id" => $userId,
                "card_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir o cartão. Tente novamente.");
            redirect("/cartoes");
        }
    }
}