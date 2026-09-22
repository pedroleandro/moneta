<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Recurrence;

class RecurrenceController extends Controller
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
            $recurrences = Recurrence::findAllForUser($userId);

            echo $this->view->render("recurrences/index", [
                "title" => "Recorrências | " . APP_NAME,
                "active" => "recorrencias",
                "recurrences" => $recurrences,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar recorrências", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar suas recorrências.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        $userId = Auth::user()->id;

        echo $this->view->render("recurrences/create", [
            "title" => "Nova Recorrência | " . APP_NAME,
            "active" => "recorrencias",
            "categories" => Category::findAllForUser($userId),
            "accounts" => BankAccount::findAllForUser($userId),
            "cards" => CreditCard::findAllForUser($userId),
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/recorrencias/nova");

        $userId = Auth::user()->id;

        try {
            [$bankAccountId, $creditCardId, $error] = $this->resolvePaymentMethod($data, $userId);

            if ($error) {
                flash_old($data);
                Message::error($error);
                redirect("/recorrencias/nova");
                return;
            }

            $recurrence = new Recurrence();
            $recurrence->fill([
                "user_id" => $userId,
                "category_id" => $data["category_id"] ?? "",
                "bank_account_id" => $bankAccountId,
                "credit_card_id" => $creditCardId,
                "type" => $data["type"] ?? "",
                "description" => trim($data["description"] ?? ""),
                "amount" => $data["amount"] ?? "0",
                "frequency" => Recurrence::FREQUENCY_MONTHLY,
                "day_of_month" => $data["day_of_month"] ?? "",
                "start_date" => $data["start_date"] ?? "",
                "end_date" => !empty($data["end_date"]) ? $data["end_date"] : null,
                "is_active" => true,
            ]);

            $errors = $recurrence->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/recorrencias/nova");
                return;
            }

            $recurrence->validateAccountOrCard();

            $recurrence->fill([
                "next_occurrence_date" => $recurrence->calculateFirstOccurrenceDate(),
            ]);

            $recurrence->save();

            AuditLog::record(LogEvent::RECURRENCE_CREATED, $userId, [
                "recurrence_id" => $recurrence->getId(),
                "description" => $recurrence->getDescription(),
            ]);

            clear_old();

            Message::success("Recorrência criada com sucesso.");
            redirect("/recorrencias");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/recorrencias/nova");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar recorrência", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar a recorrência. Tente novamente.");
            redirect("/recorrencias/nova");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $recurrence = Recurrence::findByIdForUser($id, $userId);

            if (!$recurrence) {
                Message::error("Recorrência não encontrada.");
                redirect("/recorrencias");
                return;
            }

            echo $this->view->render("recurrences/edit", [
                "title" => "Editar Recorrência | " . APP_NAME,
                "active" => "recorrencias",
                "recurrence" => $recurrence,
                "categories" => Category::findAllForUser($userId),
                "accounts" => BankAccount::findAllForUser($userId),
                "cards" => CreditCard::findAllForUser($userId),
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar recorrência para edição", [
                "user_id" => $userId,
                "recurrence_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a recorrência.");
            redirect("/recorrencias");
        }
    }

    /**
     * Editar uma recorrência só afeta ocorrências FUTURAS — o script do cron
     * usa o mesmo Model, então basta que as próximas execuções leiam os
     * campos já atualizados. Ocorrências (transactions) já criadas não são
     * tocadas aqui.
     */
    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/recorrencias/{$id}/editar");

        try {
            $recurrence = Recurrence::findByIdForUser($id, $userId);

            if (!$recurrence) {
                Message::error("Recorrência não encontrada.");
                redirect("/recorrencias");
                return;
            }

            [$bankAccountId, $creditCardId, $error] = $this->resolvePaymentMethod($data, $userId);

            if ($error) {
                flash_old($data);
                Message::error($error);
                redirect("/recorrencias/{$id}/editar");
                return;
            }

            $recurrence->fill([
                "category_id" => $data["category_id"] ?? "",
                "bank_account_id" => $bankAccountId,
                "credit_card_id" => $creditCardId,
                "type" => $data["type"] ?? "",
                "description" => trim($data["description"] ?? ""),
                "amount" => $data["amount"] ?? "0",
                "day_of_month" => $data["day_of_month"] ?? "",
                "end_date" => !empty($data["end_date"]) ? $data["end_date"] : null,
                "is_active" => !empty($data["is_active"]),
            ]);

            $errors = $recurrence->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/recorrencias/{$id}/editar");
                return;
            }

            $recurrence->validateAccountOrCard();

            $recurrence->save();

            AuditLog::record(LogEvent::RECURRENCE_UPDATED, $userId, [
                "recurrence_id" => $recurrence->getId(),
                "description" => $recurrence->getDescription(),
            ]);

            clear_old();

            Message::success("Recorrência atualizada com sucesso. Só as próximas ocorrências são afetadas.");
            redirect("/recorrencias");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/recorrencias/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar recorrência", [
                "user_id" => $userId,
                "recurrence_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar a recorrência. Tente novamente.");
            redirect("/recorrencias/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/recorrencias");

        try {
            $recurrence = Recurrence::findByIdForUser($id, $userId);

            if (!$recurrence) {
                Message::error("Recorrência não encontrada.");
                redirect("/recorrencias");
                return;
            }

            if (!$recurrence->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir essa recorrência.");
                redirect("/recorrencias");
                return;
            }

            $recurrence->delete();

            AuditLog::record(LogEvent::RECURRENCE_DELETED, $userId, [
                "recurrence_id" => $id,
                "description" => $recurrence->getDescription(),
            ]);

            Message::success("Recorrência excluída com sucesso. Ocorrências já criadas não são afetadas.");
            redirect("/recorrencias");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir recorrência", [
                "user_id" => $userId,
                "recurrence_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a recorrência. Tente novamente.");
            redirect("/recorrencias");
        }
    }

    /**
     * Mesmo padrão de validação usado em TransactionController::validatePaymentMethod().
     *
     * @return array{0: ?int, 1: ?int, 2: ?string} [bankAccountId, creditCardId, mensagemDeErro]
     */
    private function resolvePaymentMethod(array $data, int $userId): array
    {
        $bankAccountId = !empty($data["bank_account_id"]) ? (int)$data["bank_account_id"] : null;
        $creditCardId = !empty($data["credit_card_id"]) ? (int)$data["credit_card_id"] : null;

        if ($bankAccountId && $creditCardId) {
            return [null, null, "Selecione apenas uma forma de pagamento: conta OU cartão, não os dois."];
        }

        if (!$bankAccountId && !$creditCardId) {
            return [null, null, "Selecione uma conta bancária ou um cartão para a recorrência."];
        }

        if ($bankAccountId) {
            $bankAccount = BankAccount::findByIdForUser($bankAccountId, $userId);

            if (!$bankAccount) {
                return [null, null, "Conta bancária inválida."];
            }

            if (!$bankAccount->isActive()) {
                return [null, null, "Essa conta está inativa e não pode receber recorrências."];
            }
        }

        if ($creditCardId) {
            $creditCard = CreditCard::findByIdForUser($creditCardId, $userId);

            if (!$creditCard) {
                return [null, null, "Cartão inválido."];
            }

            if (!$creditCard->isActive()) {
                return [null, null, "Esse cartão está inativo e não pode receber recorrências."];
            }

            if (($data["type"] ?? "") === Recurrence::TYPE_INCOME) {
                return [null, null, "Não é possível criar uma recorrência de receita no cartão de crédito."];
            }
        }

        return [$bankAccountId, $creditCardId, null];
    }
}