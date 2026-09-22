<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../config/app.php";

use App\Core\Logger;
use App\Models\BankAccount;
use App\Models\CardInvoice;
use App\Models\CreditCard;
use App\Models\Recurrence;
use App\Models\Transaction;
use App\Models\UserNotice;

Logger::info("Recorrências: início da execução do cron");

/**
 * PASSO A — Cria novas ocorrências de recorrências vencidas.
 */
$recurrences = Recurrence::findDueForProcessing();

foreach ($recurrences as $recurrence) {
    try {
        $transaction = new Transaction();
        $transaction->fill([
            "user_id" => $recurrence->getUserId(),
            "category_id" => $recurrence->getCategoryId(),
            "type" => $recurrence->getType(),
            "description" => $recurrence->getDescription(),
            "amount" => $recurrence->getAmount(),
            "transaction_date" => $recurrence->getNextOccurrenceDate(),
            "status" => Transaction::STATUS_PENDING,
        ]);
        $transaction->fill(["recurrence_id" => $recurrence->getId()]);

        if ($recurrence->isCardRecurrence()) {
            $creditCard = CreditCard::find($recurrence->getCreditCardId());

            if (!$creditCard) {
                throw new \RuntimeException("Cartão {$recurrence->getCreditCardId()} não encontrado.");
            }

            $invoice = $creditCard->resolveInvoiceForDate($recurrence->getNextOccurrenceDate());

            $transaction->fill([
                "credit_card_id" => $creditCard->getId(),
                "card_invoice_id" => $invoice->getId(),
            ]);
        } else {
            $transaction->fill(["bank_account_id" => $recurrence->getBankAccountId()]);
        }

        $transaction->save();

        if ($transaction->getCardInvoiceId()) {
            CardInvoice::find($transaction->getCardInvoiceId())?->recalculateTotal();
        }

        $next = $recurrence->calculateNextOccurrenceDate($recurrence->getNextOccurrenceDate());

        if ($recurrence->getEndDate() && $next > $recurrence->getEndDate()) {
            $recurrence->fill(["is_active" => false]);
        } else {
            $recurrence->fill(["next_occurrence_date" => $next]);
        }

        $recurrence->save();

        Logger::info("Recorrência processada com sucesso", [
            "recurrence_id" => $recurrence->getId(),
            "transaction_date" => $transaction->getTransactionDate(),
        ]);
    } catch (\Throwable $exception) {
        $isDuplicate = str_contains($exception->getMessage(), "Duplicate entry")
            || ($exception->getPrevious() instanceof \PDOException
                && ($exception->getPrevious()->errorInfo[1] ?? null) === 1062);

        if ($isDuplicate) {
            Logger::info("Ocorrência já existia (proteção contra duplicidade acionada)", [
                "recurrence_id" => $recurrence->getId(),
            ]);
            continue;
        }

        Logger::error("Falha ao processar recorrência", [
            "recurrence_id" => $recurrence->getId(),
            "exception" => $exception->getMessage(),
        ]);
    }
}

/**
 * PASSO B — Confirma qualquer transação pendente de cartão cuja fatura fechou hoje
 * (recorrente ou não).
 */
try {
    $statement = \App\Core\Connection::getInstance()->prepare(
        "SELECT * FROM card_invoices
         WHERE status = 'aberta' AND closing_date <= CURDATE() AND deleted_at IS NULL"
    );
    $statement->execute();

    foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $invoice = CardInvoice::hydrate($row);

        try {
            $invoice->closeIfDue();

            $pending = Transaction::findAllForInvoice($invoice->getId());

            foreach ($pending as $transaction) {
                if ($transaction->getStatus() !== Transaction::STATUS_PENDING) {
                    continue;
                }

                $transaction->fill(["status" => Transaction::STATUS_CONFIRMED]);
                $transaction->save();
            }

            Logger::info("Fatura fechada e pendências confirmadas", ["invoice_id" => $invoice->getId()]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao confirmar pendências de fatura", [
                "invoice_id" => $invoice->getId(),
                "exception" => $exception->getMessage(),
            ]);
        }
    }
} catch (\Throwable $exception) {
    Logger::error("Falha ao buscar faturas fechando hoje", ["exception" => $exception->getMessage()]);
}

/**
 * PASSO C — Confirma recorrências de conta bancária vencidas, mesmo sem saldo.
 */
try {
    $statement = \App\Core\Connection::getInstance()->prepare(
        "SELECT * FROM transactions
         WHERE recurrence_id IS NOT NULL AND bank_account_id IS NOT NULL
           AND status = 'pendente' AND transaction_date <= CURDATE() AND deleted_at IS NULL"
    );
    $statement->execute();

    $noticesByUser = [];

    foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $transaction = Transaction::hydrate($row);

        try {
            $transaction->fill(["status" => Transaction::STATUS_CONFIRMED]);
            $transaction->save();
            $transaction->applyBalanceEffect();

            $account = BankAccount::find($transaction->getBankAccountId());

            $noticesByUser[$transaction->getUserId()][] = [
                "description" => $transaction->getDescription(),
                "amount" => $transaction->getAmount(),
                "type" => $transaction->getType(),
                "date" => $transaction->getTransactionDate(),
                "account_name" => $account?->getName(),
                "account_went_negative" => $account && $account->getCurrentBalance() < 0,
            ];

            Logger::info("Recorrência de conta confirmada automaticamente", [
                "transaction_id" => $transaction->getId(),
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao confirmar recorrência de conta bancária", [
                "transaction_id" => $transaction->getId(),
                "exception" => $exception->getMessage(),
            ]);
        }
    }

    foreach ($noticesByUser as $userId => $items) {
        $notice = new UserNotice();
        $notice->fill([
            "user_id" => $userId,
            "type" => UserNotice::TYPE_RECURRENCE_AUTO_CONFIRMED,
            "payload" => $items,
        ]);
        $notice->save();
    }
} catch (\Throwable $exception) {
    Logger::error("Falha ao processar recorrências de conta bancária", ["exception" => $exception->getMessage()]);
}

Logger::info("Recorrências: fim da execução do cron");
