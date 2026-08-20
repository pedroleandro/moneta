<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\BankAccount;

class BankAccountController extends Controller
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
            $accounts = BankAccount::findAllForUser($userId);

            echo $this->view->render("accounts/index", [
                "title" => "Contas Bancárias | " . APP_NAME,
                "active" => "contas",
                "accounts" => $accounts,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar contas bancárias", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar suas contas.");
            redirect("/dashboard");
        }
    }

    /**
     * @throws \JsonException
     */
    public function create(): void
    {
        $banks = \App\Core\BankApiService::getBanks();

        echo $this->view->render("accounts/create", [
            "title" => "Nova Conta | " . APP_NAME,
            "active" => "contas",
            "banks" => $banks,
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/contas/nova");

        $userId = Auth::user()->id;

        try {
            $account = new BankAccount();
            $account->fill([
                "user_id" => $userId,
                "name" => trim($data["name"] ?? ""),
                "type" => $data["type"] ?? "",
                "bank_name" => $data["bank_name"] ?? null,
                "initial_balance" => $data["initial_balance"] ?? "0",
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
                "is_active" => true,
            ]);

            $errors = $account->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/contas/nova");
                return;
            }

            $account->save();

            AuditLog::record(LogEvent::BANK_ACCOUNT_CREATED, $userId, [
                "account_id" => $account->getId(),
                "name" => $account->getName(),
            ]);

            clear_old();

            Message::success("Conta criada com sucesso.");
            redirect("/contas");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/contas/nova");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar conta bancária", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar a conta. Tente novamente.");
            redirect("/contas/nova");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $account = BankAccount::findByIdForUser($id, $userId);

            if (!$account) {
                Message::error("Conta não encontrada.");
                redirect("/contas");
                return;
            }

            $banks = \App\Core\BankApiService::getBanks();

            echo $this->view->render("accounts/edit", [
                "title" => "Editar Conta | " . APP_NAME,
                "active" => "contas",
                "account" => $account,
                "banks" => $banks,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar conta para edição", [
                "user_id" => $userId,
                "account_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a conta.");
            redirect("/contas");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/contas/{$id}/editar");

        try {
            $account = BankAccount::findByIdForUser($id, $userId);

            if (!$account) {
                Message::error("Conta não encontrada.");
                redirect("/contas");
                return;
            }

            $fillData = [
                "name" => trim($data["name"] ?? ""),
                "type" => $data["type"] ?? "",
                "bank_name" => $data["bank_name"] ?? null,
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
                "is_active" => !empty($data["is_active"]),
            ];

            if (!$account->isInUse() && isset($data["initial_balance"])) {
                $fillData["initial_balance"] = $data["initial_balance"];
            }

            $account->fill($fillData);

            if (!$account->isInUse()) {
                $account->syncCurrentBalanceWithInitial();
            }

            $errors = $account->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/contas/{$id}/editar");
                return;
            }

            $account->save();

            AuditLog::record(LogEvent::BANK_ACCOUNT_UPDATED, $userId, [
                "account_id" => $account->getId(),
                "name" => $account->getName(),
            ]);

            clear_old();

            Message::success("Conta atualizada com sucesso.");
            redirect("/contas");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/contas/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar conta bancária", [
                "user_id" => $userId,
                "account_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar a conta. Tente novamente.");
            redirect("/contas/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/contas");

        try {
            $account = BankAccount::findByIdForUser($id, $userId);

            if (!$account) {
                Message::error("Conta não encontrada.");
                redirect("/contas");
                return;
            }

            if (!$account->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir essa conta.");
                redirect("/contas");
                return;
            }

            if ($account->isInUse()) {
                Message::error(
                    "Essa conta não pode ser excluída porque já está vinculada a lançamentos, " .
                    "recorrências ou transferências."
                );
                redirect("/contas");
                return;
            }

            $account->delete();

            AuditLog::record(LogEvent::BANK_ACCOUNT_DELETED, $userId, [
                "account_id" => $id,
                "name" => $account->getName(),
            ]);

            Message::success("Conta excluída com sucesso.");
            redirect("/contas");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir conta bancária", [
                "user_id" => $userId,
                "account_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a conta. Tente novamente.");
            redirect("/contas");
        }
    }
}