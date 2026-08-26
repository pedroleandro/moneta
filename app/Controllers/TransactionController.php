<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Connection;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\BankAccount;
use App\Models\CardInvoice;
use App\Models\CardUser;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\TransactionSplit;

class TransactionController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireLogin();
    }

    public function index(?array $data): void
    {
        try {
            $userId = Auth::user()->id;
            $type = $_GET["tipo"] ?? $data["tipo"] ?? null;

            if ($type && !in_array($type, [Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE], true)) {
                $type = null;
            }

            $transactions = Transaction::findAllForUser($userId, $type);

            $activeSlug = match ($type) {
                Transaction::TYPE_INCOME => "lancamentos-receitas",
                Transaction::TYPE_EXPENSE => "lancamentos-despesas",
                default => "lancamentos-todos",
            };

            echo $this->view->render("transactions/index", [
                "title" => "Lançamentos | " . APP_NAME,
                "active" => $activeSlug,
                "transactions" => $transactions,
                "filterType" => $type,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar lançamentos", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seus lançamentos.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        try {
            $userId = Auth::user()->id;

            $accounts = array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive()));
            $cards = array_values(array_filter(CreditCard::findAllForUser($userId), fn($c) => $c->isActive()));
            $categories = Category::findAllForUser($userId);
            $cardUsers = CardUser::findAllForUser($userId);

            if (empty($accounts) && empty($cards)) {
                Message::warning("Cadastre uma conta bancária ativa ou um cartão ativo antes de lançar algo.");
                redirect("/contas/nova");
                return;
            }

            echo $this->view->render("transactions/create", [
                "title" => "Novo Lançamento | " . APP_NAME,
                "active" => "lancamentos-todos",
                "accounts" => $accounts,
                "cards" => $cards,
                "categories" => $categories,
                "cardUsers" => $cardUsers,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar formulário de lançamento", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o formulário.");
            redirect("/lancamentos");
        }
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/lancamentos/novo");

        $userId = Auth::user()->id;
        $connection = Connection::getInstance();

        try {
            $paymentCheck = $this->validatePaymentMethod($data, $userId);

            if (is_string($paymentCheck)) {
                flash_old($data);
                Message::error($paymentCheck);
                redirect("/lancamentos/novo");
                return;
            }

            [$bankAccount, $creditCard] = $paymentCheck;

            $categoryCheck = $this->validateCategory($data, $userId);

            if (is_string($categoryCheck)) {
                flash_old($data);
                Message::error($categoryCheck);
                redirect("/lancamentos/novo");
                return;
            }

            $fundsCheck = $this->validateSufficientFunds($data, $bankAccount, $creditCard);
            if (is_string($fundsCheck)) {
                flash_old($data);
                Message::error($fundsCheck);
                redirect("/lancamentos/novo");
                return;
            }

            $dateCheck = $this->validateTransactionDate($data, $bankAccount, $creditCard);
            if (is_string($dateCheck)) {
                flash_old($data);
                Message::error($dateCheck);
                redirect("/lancamentos/novo");
                return;
            }

            $connection->beginTransaction();

            try {
                $transaction = new Transaction();
                $transaction->fill([
                    "user_id" => $userId,
                    "category_id" => $categoryCheck->getId(),
                    "bank_account_id" => $bankAccount?->getId(),
                    "credit_card_id" => $creditCard?->getId(),
                    "type" => $data["type"] ?? "",
                    "description" => trim($data["description"] ?? ""),
                    "amount" => $data["amount"] ?? "0",
                    "transaction_date" => $data["transaction_date"] ?? "",
                    "status" => $data["status"] ?? Transaction::STATUS_PENDING,
                ]);

                $errors = $transaction->validate($data);

                if ($errors) {
                    $connection->rollBack();
                    flash_old($data);
                    Message::error(implode(" ", $errors));
                    redirect("/lancamentos/novo");
                    return;
                }

                if ($creditCard) {
                    $invoice = $creditCard->resolveInvoiceForDate($transaction->getTransactionDate());

                    if ($invoice->getStatus() === \App\Models\CardInvoice::STATUS_PAID) {
                        $connection->rollBack();
                        flash_old($data);
                        Message::error("Essa data cai numa fatura que já foi paga. Não é possível adicionar lançamento nela.");
                        redirect("/lancamentos/novo");
                        return;
                    }

                    $transaction->setCardInvoiceId($invoice->getId());
                    $transaction->setTransactionDate($invoice->getDueDate());
                }

                $transaction->save();

                if ($creditCard) {
                    $splitsResult = CardUser::validateSplitAssignments(
                        $data["split_card_user_id"] ?? [],
                        $data["split_amount"] ?? [],
                        $userId,
                        $creditCard->getId(),
                        $transaction->getAmount()
                    );

                    if (is_string($splitsResult)) {
                        $connection->rollBack();
                        flash_old($data);
                        Message::error($splitsResult);
                        redirect("/lancamentos/novo");
                        return;
                    }

                    foreach ($splitsResult as $split) {
                        $transactionSplit = new TransactionSplit();
                        $transactionSplit->fill([
                            "transaction_id" => $transaction->getId(),
                            "card_user_id" => $split["card_user_id"],
                            "amount" => $split["amount"],
                        ]);
                        $transactionSplit->save();
                    }
                }

                $transaction->applyBalanceEffect();
                $transaction->refreshInvoiceTotal();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::TRANSACTION_CREATED, $userId, [
                "transaction_id" => $transaction->getId(),
                "type" => $transaction->getType(),
                "amount" => $transaction->getAmount(),
            ]);

            clear_old();

            Message::success("Lançamento criado com sucesso.");
            redirect("/lancamentos");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/lancamentos/novo");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar lançamento", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar o lançamento. Tente novamente.");
            redirect("/lancamentos/novo");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $transaction = Transaction::findByIdForUser($id, $userId);

            if (!$transaction) {
                Message::error("Lançamento não encontrado.");
                redirect("/lancamentos");
                return;
            }

            if ($transaction->getType() === Transaction::TYPE_TRANSFER) {
                Message::error("Transferências não podem ser editadas. Exclua e crie uma nova, se necessário.");
                redirect("/lancamentos");
                return;
            }

            $accounts = BankAccount::findAllForUser($userId);
            $cards = CreditCard::findAllForUser($userId);
            $categories = Category::findAllForUser($userId);
            $cardUsers = CardUser::findAllForUser($userId);
            $splits = TransactionSplit::findAllForTransaction($id);

            echo $this->view->render("transactions/edit", [
                "title" => "Editar Lançamento | " . APP_NAME,
                "active" => $active ?? "lancamentos-todos",
                "transaction" => $transaction,
                "accounts" => $accounts,
                "cards" => $cards,
                "categories" => $categories,
                "cardUsers" => $cardUsers,
                "splits" => $splits,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar lançamento para edição", [
                "user_id" => $userId,
                "transaction_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o lançamento.");
            redirect("/lancamentos");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/lancamentos/{$id}/editar");

        $connection = Connection::getInstance();

        try {
            $transaction = Transaction::findByIdForUser($id, $userId);

            if (!$transaction) {
                Message::error("Lançamento não encontrado.");
                redirect("/lancamentos");
                return;
            }

            if ($transaction->getInstallmentPurchaseId()) {
                $data["transaction_date"] = $transaction->getTransactionDate();
                $data["credit_card_id"] = $transaction->getCreditCardId();
                $data["bank_account_id"] = null;
            }

            $paymentCheck = $this->validatePaymentMethod($data, $userId);

            if (is_string($paymentCheck)) {
                flash_old($data);
                Message::error($paymentCheck);
                redirect("/lancamentos/{$id}/editar");
                return;
            }

            [$bankAccount, $creditCard] = $paymentCheck;

            $categoryCheck = $this->validateCategory($data, $userId);

            if (is_string($categoryCheck)) {
                flash_old($data);
                Message::error($categoryCheck);
                redirect("/lancamentos/{$id}/editar");
                return;
            }

            $fundsCheck = $this->validateSufficientFunds($data, $bankAccount, $creditCard, $transaction);
            if (is_string($fundsCheck)) {
                flash_old($data);
                Message::error($fundsCheck);
                redirect("/lancamentos/{$id}/editar");
                return;
            }

            $dateCheck = $this->validateTransactionDate($data, $bankAccount, $creditCard, $transaction);
            if (is_string($dateCheck)) {
                flash_old($data);
                Message::error($dateCheck);
                redirect("/lancamentos/{$id}/editar");
                return;
            }

            $connection->beginTransaction();

            try {
                $oldInvoiceId = $transaction->getCardInvoiceId();
                $transaction->reverseBalanceEffect();

                $transaction->fill([
                    "category_id" => $categoryCheck->getId(),
                    "bank_account_id" => $bankAccount?->getId(),
                    "credit_card_id" => $creditCard?->getId(),
                    "type" => $data["type"] ?? "",
                    "description" => trim($data["description"] ?? ""),
                    "amount" => $data["amount"] ?? "0",
                    "transaction_date" => $data["transaction_date"] ?? "",
                    "status" => $data["status"] ?? Transaction::STATUS_PENDING,
                ]);

                $errors = $transaction->validate($data);

                if ($errors) {
                    $connection->rollBack();
                    flash_old($data);
                    Message::error(implode(" ", $errors));
                    redirect("/lancamentos/{$id}/editar");
                    return;
                }

                if ($creditCard) {
                    if (!$transaction->getInstallmentPurchaseId()) {
                        $invoice = $creditCard->resolveInvoiceForDate($transaction->getTransactionDate());

                        if ($invoice->getStatus() === \App\Models\CardInvoice::STATUS_PAID) {
                            $connection->rollBack();
                            flash_old($data);
                            Message::error("Essa data cai numa fatura que já foi paga. Não é possível atualizar lançamento nela.");
                            redirect("/lancamentos/{$id}/editar");
                            return;
                        }

                        $transaction->setCardInvoiceId($invoice->getId());
                        $transaction->setTransactionDate($invoice->getDueDate());
                    }
                } else {
                    $transaction->setCardInvoiceId(null);
                }

                TransactionSplit::deleteAllForTransaction($id);

                if ($creditCard) {
                    $splitsResult = CardUser::validateSplitAssignments(
                        $data["split_card_user_id"] ?? [],
                        $data["split_amount"] ?? [],
                        $userId,
                        $creditCard->getId(),
                        $transaction->getAmount()
                    );

                    if (is_string($splitsResult)) {
                        $connection->rollBack();
                        flash_old($data);
                        Message::error($splitsResult);
                        redirect("/lancamentos/{$id}/editar");
                        return;
                    }

                    foreach ($splitsResult as $split) {
                        $transactionSplit = new TransactionSplit();
                        $transactionSplit->fill([
                            "transaction_id" => $id,
                            "card_user_id" => $split["card_user_id"],
                            "amount" => $split["amount"],
                        ]);
                        $transactionSplit->save();
                    }
                }

                $transaction->save();
                $transaction->applyBalanceEffect();

                if ($oldInvoiceId && $oldInvoiceId !== $transaction->getCardInvoiceId()) {
                    $oldInvoice = CardInvoice::find($oldInvoiceId);
                    $oldInvoice?->recalculateTotal();
                }

                $transaction->refreshInvoiceTotal();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::TRANSACTION_UPDATED, $userId, [
                "transaction_id" => $transaction->getId(),
            ]);

            clear_old();

            Message::success("Lançamento atualizado com sucesso.");
            redirect("/lancamentos");
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/lancamentos/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar lançamento", [
                "user_id" => $userId,
                "transaction_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar o lançamento. Tente novamente.");
            redirect("/lancamentos/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/lancamentos");

        $connection = Connection::getInstance();

        try {
            $transaction = Transaction::findByIdForUser($id, $userId);

            if (!$transaction) {
                Message::error("Lançamento não encontrado.");
                redirect("/lancamentos");
                return;
            }

            if (!$transaction->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir esse lançamento.");
                redirect("/lancamentos");
                return;
            }

            $connection->beginTransaction();

            try {
                $invoiceId = $transaction->getCardInvoiceId();

                TransactionSplit::deleteAllForTransaction($id);
                $transaction->reverseBalanceEffect();
                $transaction->delete();

                if ($invoiceId) {
                    $invoice = CardInvoice::find($invoiceId);
                    $invoice?->recalculateTotal();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::TRANSACTION_DELETED, $userId, [
                "transaction_id" => $id,
            ]);

            Message::success("Lançamento excluído com sucesso.");
            redirect("/lancamentos");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir lançamento", [
                "user_id" => $userId,
                "transaction_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir o lançamento. Tente novamente.");
            redirect("/lancamentos");
        }
    }

    private function validatePaymentMethod(array $data, int $userId): array|string
    {
        $bankAccountId = !empty($data["bank_account_id"]) ? (int)$data["bank_account_id"] : null;
        $creditCardId = !empty($data["credit_card_id"]) ? (int)$data["credit_card_id"] : null;

        if ($bankAccountId && $creditCardId) {
            return "Selecione apenas uma forma de pagamento: conta OU cartão, não os dois.";
        }

        if (!$bankAccountId && !$creditCardId) {
            return "Selecione uma conta bancária ou um cartão para o lançamento.";
        }

        $bankAccount = null;
        $creditCard = null;

        if ($bankAccountId) {
            $bankAccount = BankAccount::findByIdForUser($bankAccountId, $userId);

            if (!$bankAccount) {
                return "Conta bancária inválida.";
            }

            if (!$bankAccount->isActive()) {
                return "Essa conta está inativa e não pode receber novos lançamentos.";
            }
        }

        if ($creditCardId) {
            $creditCard = CreditCard::findByIdForUser($creditCardId, $userId);

            if (!$creditCard) {
                return "Cartão inválido.";
            }

            if (!$creditCard->isActive()) {
                return "Esse cartão está inativo e não pode receber novos lançamentos.";
            }

            if (($data["type"] ?? "") === Transaction::TYPE_INCOME) {
                return "Não é possível lançar uma receita no cartão de crédito. Receitas entram numa conta bancária.";
            }
        }

        return [$bankAccount, $creditCard];
    }

    private function validateSufficientFunds(
        array        $data,
        ?BankAccount $bankAccount,
        ?CreditCard  $creditCard,
        ?Transaction $existingTransaction = null
    ): true|string
    {
        $type = $data["type"] ?? "";
        $status = $data["status"] ?? Transaction::STATUS_PENDING;
        $amount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

        if ($type !== Transaction::TYPE_EXPENSE) {
            return true;
        }

        if ($bankAccount && $status === Transaction::STATUS_CONFIRMED) {
            $availableBalance = $bankAccount->getCurrentBalance();

            if (
                $existingTransaction
                && $existingTransaction->getBankAccountId() === $bankAccount->getId()
                && $existingTransaction->isConfirmed()
            ) {
                $availableBalance += $existingTransaction->getAmount();
            }

            if ($amount > $availableBalance) {
                return "Saldo insuficiente. Saldo disponível: R$ " . number_format($availableBalance, 2, ',', '.') . ".";
            }
        }

        if ($creditCard) {
            $availableLimit = $creditCard->getAvailableLimit();

            if ($existingTransaction && $existingTransaction->getCreditCardId() === $creditCard->getId()) {
                $availableLimit += $existingTransaction->getAmount();
            }

            if ($amount > $availableLimit) {
                return "Esse lançamento ultrapassa o limite disponível do cartão. Disponível: R$ " . number_format($availableLimit, 2, ',', '.') . ".";
            }
        }

        return true;
    }

    private function validateCategory(array $data, int $userId): Category|string
    {
        $categoryId = (int)($data["category_id"] ?? 0);
        $type = $data["type"] ?? "";

        $category = Category::findByIdForUser($categoryId, $userId);

        if (!$category) {
            return "Categoria inválida.";
        }

        if ($category->getType() !== $type) {
            return "A categoria selecionada não é do tipo " . ($type === "receita" ? "receita" : "despesa") . ".";
        }

        return $category;
    }

    private function validateTransactionDate(
        array $data,
        ?BankAccount $bankAccount,
        ?CreditCard $creditCard,
        ?Transaction $existingTransaction = null
    ): true|string {

        if ($existingTransaction && $existingTransaction->getInstallmentPurchaseId()) {
            return true;
        }

        $dateStr = $data["transaction_date"] ?? "";
        $status = $data["status"] ?? Transaction::STATUS_PENDING;
        $type = $data["type"] ?? "";

        $date = \DateTime::createFromFormat("Y-m-d", $dateStr);

        if (!$date) {
            return true;
        }

        $today = new \DateTime("today");
        $date->setTime(0, 0, 0);

        if ($type === Transaction::TYPE_INCOME) {
            return $this->validateIncomeDate($date, $today, $status, $dateStr);
        }

        if ($date > $today) {
            return "A data do lançamento não pode ser no futuro.";
        }

        if ($date < $today && $status !== Transaction::STATUS_CONFIRMED) {
            return "Lançamentos com data no passado precisam estar como Confirmado.";
        }

        if ($date < $today) {
            $earliest = $creditCard
                ? $creditCard->getEarliestAllowedDate()
                : (new \DateTimeImmutable("first day of last month"))->format("Y-m-d");

            if ($dateStr < $earliest) {
                $earliestFormatted = date("d/m/Y", strtotime($earliest));
                return "A data está muito no passado. A data mais antiga permitida é {$earliestFormatted}.";
            }
        }

        return true;
    }

    private function validateIncomeDate(\DateTime $date, \DateTime $today, string $status, string $dateStr): true|string
    {
        if ($date > $today && $status === Transaction::STATUS_CONFIRMED) {
            return "Não é possível confirmar uma receita com data no futuro.";
        }

        if ($date < $today) {
            $earliest = (new \DateTimeImmutable("first day of last month"))->format("Y-m-d");

            if ($dateStr < $earliest) {
                $earliestFormatted = date("d/m/Y", strtotime($earliest));
                return "A data está muito no passado. A data mais antiga permitida é {$earliestFormatted}.";
            }
        }

        if ($date > $today) {
            $latest = (new \DateTimeImmutable("last day of next month"))->format("Y-m-d");

            if ($dateStr > $latest) {
                $latestFormatted = date("d/m/Y", strtotime($latest));
                return "A data está muito no futuro. A data mais distante permitida é {$latestFormatted}.";
            }
        }

        return true;
    }
}