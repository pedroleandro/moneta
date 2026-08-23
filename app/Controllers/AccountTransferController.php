<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Connection;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\Transaction;

class AccountTransferController extends Controller
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
            $transfers = AccountTransfer::findAllForUser($userId);

            echo $this->view->render("account_transfers/index", [
                "title" => "Transferências | " . APP_NAME,
                "active" => "lancamentos-transferencias",
                "transfers" => $transfers,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar transferências", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar suas transferências.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        try {
            $userId = Auth::user()->id;
            $accounts = array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive()));

            if (count($accounts) < 2) {
                Message::warning("Você precisa de pelo menos 2 contas bancárias ativas para fazer uma transferência.");
                redirect("/contas");
                return;
            }

            echo $this->view->render("account_transfers/create", [
                "title" => "Nova Transferência | " . APP_NAME,
                "active" => "lancamentos-transferencias",
                "accounts" => $accounts,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar formulário de transferência", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o formulário.");
            redirect("/transferencias");
        }
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/transferencias/nova");

        $userId = Auth::user()->id;
        $fromAccountId = (int)($data["from_account_id"] ?? 0);
        $toAccountId = (int)($data["to_account_id"] ?? 0);

        try {
            if ($fromAccountId === $toAccountId) {
                flash_old($data);
                Message::error("A conta de origem e destino não podem ser a mesma.");
                redirect("/transferencias/nova");
                return;
            }

            $fromAccount = BankAccount::findByIdForUser($fromAccountId, $userId);
            $toAccount = BankAccount::findByIdForUser($toAccountId, $userId);

            if (!$fromAccount || !$toAccount) {
                flash_old($data);
                Message::error("Conta de origem ou destino inválida.");
                redirect("/transferencias/nova");
                return;
            }

            if (!$fromAccount->isActive() || !$toAccount->isActive()) {
                flash_old($data);
                Message::error("Ambas as contas precisam estar ativas.");
                redirect("/transferencias/nova");
                return;
            }

            $amount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

            if ($amount > $fromAccount->getCurrentBalance()) {
                flash_old($data);
                Message::error(
                    "Saldo insuficiente na conta de origem. Saldo disponível: R$ " .
                    number_format($fromAccount->getCurrentBalance(), 2, ',', '.') . "."
                );
                redirect("/transferencias/nova");
                return;
            }

            $transfer = new AccountTransfer();
            $transfer->fill([
                "user_id" => $userId,
                "from_account_id" => $fromAccountId,
                "to_account_id" => $toAccountId,
                "amount" => $data["amount"] ?? "0",
                "transfer_date" => $data["transfer_date"] ?? "",
                "description" => $data["description"] ?? null,
            ]);

            $errors = $transfer->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/transferencias/nova");
                return;
            }

            $connection = Connection::getInstance();
            $connection->beginTransaction();

            try {
                $transfer->save();

                $description = $transfer->getDescription() ?: "Transferência entre contas";

                $outgoing = new Transaction();
                $outgoing->fill([
                    "user_id" => $userId,
                    "category_id" => null,
                    "bank_account_id" => $fromAccountId,
                    "transfer_id" => $transfer->getId(),
                    "type" => Transaction::TYPE_TRANSFER,
                    "description" => $description,
                    "amount" => $transfer->getAmount(),
                    "transaction_date" => $transfer->getTransferDate(),
                    "status" => "confirmado",
                ]);
                $outgoing->save();

                $incoming = new Transaction();
                $incoming->fill([
                    "user_id" => $userId,
                    "category_id" => null,
                    "bank_account_id" => $toAccountId,
                    "transfer_id" => $transfer->getId(),
                    "type" => Transaction::TYPE_TRANSFER,
                    "description" => $description,
                    "amount" => $transfer->getAmount(),
                    "transaction_date" => $transfer->getTransferDate(),
                    "status" => "confirmado",
                ]);
                $incoming->save();

                $fromAccount->adjustBalance(-$transfer->getAmount());
                $toAccount->adjustBalance($transfer->getAmount());

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::TRANSFER_CREATED, $userId, [
                "transfer_id" => $transfer->getId(),
                "amount" => $transfer->getAmount(),
                "from_account_id" => $fromAccountId,
                "to_account_id" => $toAccountId,
            ]);

            clear_old();

            Message::success("Transferência realizada com sucesso.");
            redirect("/transferencias");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/transferencias/nova");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao realizar transferência", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível realizar a transferência. Tente novamente.");
            redirect("/transferencias/nova");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/transferencias");

        try {
            $transfer = AccountTransfer::findByIdForUser($id, $userId);

            if (!$transfer) {
                Message::error("Transferência não encontrada.");
                redirect("/transferencias");
                return;
            }

            $connection = Connection::getInstance();
            $connection->beginTransaction();

            try {
                $statement = $connection->prepare(
                    "SELECT * FROM transactions WHERE transfer_id = :transfer_id AND deleted_at IS NULL"
                );
                $statement->execute(["transfer_id" => $id]);
                $linkedTransactions = $statement->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($linkedTransactions as $row) {
                    $bankAccountId = (int)$row["bank_account_id"];
                    $amount = (float)$row["amount"];

                    $isOutgoing = $bankAccountId === $transfer->getFromAccountId();
                    $account = BankAccount::find($bankAccountId);

                    if ($account) {
                        $account->adjustBalance($isOutgoing ? $amount : -$amount);
                    }

                    $deleteStatement = $connection->prepare(
                        "UPDATE transactions SET deleted_at = NOW() WHERE id = :id"
                    );
                    $deleteStatement->execute(["id" => $row["id"]]);
                }

                $transfer->delete();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::TRANSFER_DELETED, $userId, [
                "transfer_id" => $id,
            ]);

            Message::success("Transferência excluída com sucesso.");
            redirect("/transferencias");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir transferência", [
                "user_id" => $userId,
                "transfer_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a transferência. Tente novamente.");
            redirect("/transferencias");
        }
    }
}