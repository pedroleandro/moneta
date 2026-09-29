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
use App\Models\CardUser;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Transaction;

class LoanController extends Controller
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

            echo $this->view->render("loans/index", [
                "title" => "Empréstimos | " . APP_NAME,
                "active" => "emprestimos",
                "loans" => Loan::findAllForUser($userId),
                "accounts" => array_values(array_filter(BankAccount::findAllForUser($userId), static fn($a) => $a->isActive())),
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar empréstimos", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seus empréstimos.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        $userId = Auth::user()->id;

        echo $this->view->render("loans/create", [
            "title" => "Novo Empréstimo | " . APP_NAME,
            "active" => "emprestimos",
            "cardUsers" => CardUser::findAllForUser($userId),
            "accounts" => array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive())),
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/emprestimos/novo");

        $userId = Auth::user()->id;
        $connection = Connection::getInstance();

        try {
            $cardUser = CardUser::findByIdForUser((int)($data["card_user_id"] ?? 0), $userId);

            if (!$cardUser) {
                flash_old($data);
                Message::error("Pessoa inválida.");
                redirect("/emprestimos/novo");
                return;
            }

            $status = $data["status"] ?? Loan::STATUS_PENDING;

            if (!in_array($status, [Loan::STATUS_PENDING, Loan::STATUS_CONFIRMED], true)) {
                $status = Loan::STATUS_PENDING;
            }

            $bankAccount = null;

            if ($status === Loan::STATUS_CONFIRMED) {
                $accountCheck = $this->validateBankAccountForOutgoing($data, $userId, (float)str_replace(",", ".", (string)($data["amount"] ?? "0")));

                if (is_string($accountCheck)) {
                    flash_old($data);
                    Message::error($accountCheck);
                    redirect("/emprestimos/novo");
                    return;
                }

                $bankAccount = $accountCheck;
            }

            $connection->beginTransaction();

            try {
                $loan = new Loan();
                $loan->fill([
                    "owner_user_id" => $userId,
                    "card_user_id" => $cardUser->getId(),
                    "bank_account_id" => $bankAccount?->getId(),
                    "description" => trim($data["description"] ?? ""),
                    "amount" => $data["amount"] ?? "0",
                    "due_date" => !empty($data["due_date"]) ? $data["due_date"] : null,
                    "notes" => $data["notes"] ?? null,
                    "status" => $status,
                ]);

                $errors = $loan->validate($data);

                if ($errors) {
                    $connection->rollBack();
                    flash_old($data);
                    Message::error(implode(" ", $errors));
                    redirect("/emprestimos/novo");
                    return;
                }

                $loan->save();

                if ($bankAccount) {
                    $this->createOutgoingTransaction($loan, $bankAccount);
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::LOAN_CREATED, $userId, [
                "loan_id" => $loan->getId(),
                "amount" => $loan->getAmount(),
                "status" => $loan->getStatus(),
            ]);

            clear_old();

            Message::success("Empréstimo registrado com sucesso.");
            redirect("/emprestimos");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/emprestimos/novo");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar empréstimo", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível registrar o empréstimo. Tente novamente.");
            redirect("/emprestimos/novo");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $loan = Loan::findByIdForUser($id, $userId);

            if (!$loan) {
                Message::error("Empréstimo não encontrado.");
                redirect("/emprestimos");
                return;
            }

            echo $this->view->render("loans/edit", [
                "title" => "Editar Empréstimo | " . APP_NAME,
                "active" => "emprestimos",
                "loan" => $loan,
                "cardUsers" => CardUser::findAllForUser($userId),
                "accounts" => array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive())),
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar empréstimo para edição", [
                "user_id" => $userId,
                "loan_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o empréstimo.");
            redirect("/emprestimos");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/emprestimos/{$id}/editar");

        $connection = Connection::getInstance();

        try {
            $loan = Loan::findByIdForUser($id, $userId);

            if (!$loan) {
                Message::error("Empréstimo não encontrado.");
                redirect("/emprestimos");
                return;
            }

            if ($loan->isSettled()) {
                Message::error("Esse empréstimo já foi quitado e não pode ser editado.");
                redirect("/emprestimos");
                return;
            }

            $newAmount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

            if ($loan->getReturnedAmount() > 0 && $newAmount !== $loan->getAmount()) {
                Message::error("Esse empréstimo já tem devoluções registradas. O valor não pode mais ser alterado.");
                redirect("/emprestimos/{$id}/editar");
                return;
            }

            $newStatus = $data["status"] ?? $loan->getStatus();

            if ($loan->isPending() && $newStatus === Loan::STATUS_CONFIRMED) {
                $accountCheck = $this->validateBankAccountForOutgoing($data, $userId, $newAmount);

                if (is_string($accountCheck)) {
                    flash_old($data);
                    Message::error($accountCheck);
                    redirect("/emprestimos/{$id}/editar");
                    return;
                }

                $bankAccount = $accountCheck;
            } elseif ($loan->isConfirmed()) {
                $accountCheck = $this->validateBankAccountForOutgoing($data, $userId, $newAmount, $loan);

                if (is_string($accountCheck)) {
                    flash_old($data);
                    Message::error($accountCheck);
                    redirect("/emprestimos/{$id}/editar");
                    return;
                }

                $bankAccount = $accountCheck;
            } else {
                $bankAccount = null;
            }

            $connection->beginTransaction();

            try {
                $existingTransaction = $loan->getTransactionId() ? Transaction::find($loan->getTransactionId()) : null;

                if ($existingTransaction) {
                    $existingTransaction->reverseBalanceEffect();
                }

                $loan->fill([
                    "card_user_id" => $data["card_user_id"] ?? $loan->getCardUserId(),
                    "bank_account_id" => $bankAccount?->getId(),
                    "description" => trim($data["description"] ?? ""),
                    "amount" => $newAmount,
                    "due_date" => !empty($data["due_date"]) ? $data["due_date"] : null,
                    "notes" => $data["notes"] ?? null,
                    "status" => $newStatus,
                ]);

                $errors = $loan->validate($data);

                if ($errors) {
                    $connection->rollBack();
                    flash_old($data);
                    Message::error(implode(" ", $errors));
                    redirect("/emprestimos/{$id}/editar");
                    return;
                }

                if ($bankAccount && $existingTransaction) {
                    $existingTransaction->fill([
                        "bank_account_id" => $bankAccount->getId(),
                        "amount" => $newAmount,
                    ]);
                    $existingTransaction->save();
                    $existingTransaction->applyBalanceEffect();
                } elseif ($bankAccount && !$existingTransaction) {
                    $this->createOutgoingTransaction($loan, $bankAccount);
                }

                $loan->save();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::LOAN_UPDATED, $userId, ["loan_id" => $loan->getId()]);

            clear_old();

            Message::success("Empréstimo atualizado com sucesso.");
            redirect("/emprestimos");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/emprestimos/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar empréstimo", [
                "user_id" => $userId,
                "loan_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar o empréstimo. Tente novamente.");
            redirect("/emprestimos/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/emprestimos");

        $connection = Connection::getInstance();

        try {
            $loan = Loan::findByIdForUser($id, $userId);

            if (!$loan) {
                Message::error("Empréstimo não encontrado.");
                redirect("/emprestimos");
                return;
            }

            if ($loan->getReturnedAmount() > 0) {
                Message::error("Esse empréstimo já tem devoluções registradas. Exclua as devoluções antes.");
                redirect("/emprestimos");
                return;
            }

            $connection->beginTransaction();

            try {
                $transaction = $loan->getTransactionId() ? Transaction::find($loan->getTransactionId()) : null;

                if ($transaction) {
                    $transaction->reverseBalanceEffect();
                    $transaction->delete();
                }

                $loan->delete();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::LOAN_DELETED, $userId, ["loan_id" => $id]);

            Message::success("Empréstimo excluído com sucesso.");
            redirect("/emprestimos");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir empréstimo", [
                "user_id" => $userId,
                "loan_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir o empréstimo. Tente novamente.");
            redirect("/emprestimos");
        }
    }

    public function storePayment(?array $data): void
    {
        $userId = Auth::user()->id;
        $loanId = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/emprestimos/{$loanId}");

        $connection = Connection::getInstance();

        try {
            $loan = Loan::findByIdForUser($loanId, $userId);

            if (!$loan) {
                Message::error("Empréstimo não encontrado.");
                redirect("/emprestimos");
                return;
            }

            if (!$loan->isConfirmed()) {
                Message::error("Esse empréstimo ainda está pendente. Confirme-o antes de registrar uma devolução.");
                redirect("/emprestimos");
                return;
            }

            $amount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

            if ($amount <= 0) {
                Message::error("O valor da devolução deve ser maior que zero.");
                redirect("/emprestimos");
                return;
            }

            if ($amount > $loan->getRemainingAmount() + 0.001) {
                Message::error(
                    "O valor não pode ultrapassar o saldo em aberto (R$ " .
                    number_format($loan->getRemainingAmount(), 2, ',', '.') . ")."
                );
                redirect("/emprestimos");
                return;
            }

            $paymentDate = $data["payment_date"] ?? date("Y-m-d");

            if (!\DateTime::createFromFormat("Y-m-d", $paymentDate)) {
                Message::error("Data de devolução inválida.");
                redirect("/emprestimos");
                return;
            }

            if ($paymentDate > date("Y-m-d")) {
                Message::error("A data da devolução não pode ser no futuro.");
                redirect("/emprestimos");
                return;
            }

            $loanTransaction = Transaction::find($loan->getTransactionId());

            if ($loanTransaction && $paymentDate < $loanTransaction->getTransactionDate()) {
                Message::error("A data da devolução não pode ser anterior à data do empréstimo.");
                redirect("/emprestimos");
                return;
            }

            $bankAccount = BankAccount::findByIdForUser((int)($data["bank_account_id"] ?? 0), $userId);

            if (!$bankAccount) {
                Message::error("Conta bancária inválida.");
                redirect("/emprestimos");
                return;
            }

            if (!$bankAccount->isActive()) {
                Message::error("Essa conta está inativa.");
                redirect("/emprestimos");
                return;
            }

            $connection->beginTransaction();

            try {
                $incomeTransaction = new Transaction();
                $incomeTransaction->fill([
                    "user_id" => $userId,
                    "category_id" => null,
                    "bank_account_id" => $bankAccount->getId(),
                    "type" => Transaction::TYPE_INCOME,
                    "description" => "Devolução — " . $loan->getDescription(),
                    "amount" => $amount,
                    "transaction_date" => $paymentDate,
                    "status" => Transaction::STATUS_CONFIRMED,
                ]);
                $incomeTransaction->save();
                $incomeTransaction->applyBalanceEffect();

                $payment = new LoanPayment();
                $payment->fill([
                    "loan_id" => $loan->getId(),
                    "transaction_id" => $incomeTransaction->getId(),
                    "bank_account_id" => $bankAccount->getId(),
                    "amount" => $amount,
                    "payment_date" => $paymentDate,
                    "notes" => $data["notes"] ?? null,
                ]);
                $payment->save();

                if ($amount >= $loan->getRemainingAmount() - 0.001) {
                    $loan->setStatus(Loan::STATUS_SETTLED);
                    $loan->save();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::LOAN_PAYMENT_CREATED, $userId, [
                "loan_id" => $loan->getId(),
                "amount" => $amount,
            ]);

            Message::success("Devolução registrada com sucesso.");
            redirect("/emprestimos");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao registrar devolução de empréstimo", [
                "user_id" => $userId,
                "loan_id" => $loanId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível registrar a devolução. Tente novamente.");
            redirect("/emprestimos");
        }
    }

    public function destroyPayment(?array $data): void
    {
        $userId = Auth::user()->id;
        $loanId = (int)($data["id"] ?? 0);
        $paymentId = (int)($data["paymentId"] ?? 0);

        $this->validateCsrfToken($data, "/emprestimos");

        $connection = Connection::getInstance();

        try {
            $loan = Loan::findByIdForUser($loanId, $userId);
            $payment = LoanPayment::findByIdForUser($paymentId, $userId);

            if (!$loan || !$payment || $payment->getLoanId() !== $loan->getId()) {
                Message::error("Devolução não encontrada.");
                redirect("/emprestimos");
                return;
            }

            $connection->beginTransaction();

            try {
                $transaction = $payment->getTransactionId() ? Transaction::find($payment->getTransactionId()) : null;

                if ($transaction) {
                    $transaction->reverseBalanceEffect();
                    $transaction->delete();
                }

                $payment->delete();

                if ($loan->isSettled()) {
                    $loan->setStatus(Loan::STATUS_CONFIRMED);
                    $loan->save();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::LOAN_PAYMENT_DELETED, $userId, [
                "loan_id" => $loanId,
                "payment_id" => $paymentId,
            ]);

            Message::success("Devolução excluída com sucesso.");
            redirect("/emprestimos");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir devolução de empréstimo", [
                "user_id" => $userId,
                "loan_id" => $loanId,
                "payment_id" => $paymentId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a devolução. Tente novamente.");
            redirect("/emprestimos");
        }
    }

    private function validateBankAccountForOutgoing(
        array $data,
        int $userId,
        float $amount,
        ?Loan $existingLoan = null
    ): BankAccount|string {
        $bankAccountId = !empty($data["bank_account_id"]) ? (int)$data["bank_account_id"] : null;

        if (!$bankAccountId) {
            return "Selecione a conta de onde o dinheiro vai sair.";
        }

        $bankAccount = BankAccount::findByIdForUser($bankAccountId, $userId);

        if (!$bankAccount) {
            return "Conta bancária inválida.";
        }

        if (!$bankAccount->isActive()) {
            return "Essa conta está inativa.";
        }

        $availableBalance = $bankAccount->getCurrentBalance();

        if ($existingLoan && $existingLoan->getBankAccountId() === $bankAccount->getId()) {
            $availableBalance += $existingLoan->getAmount();
        }

        if ($amount > $availableBalance) {
            return "Saldo insuficiente. Saldo disponível: R$ " . number_format($availableBalance, 2, ',', '.') . ".";
        }

        return $bankAccount;
    }

    private function createOutgoingTransaction(Loan $loan, BankAccount $bankAccount): void
    {
        $transaction = new Transaction();
        $transaction->fill([
            "user_id" => $loan->getOwnerUserId(),
            "category_id" => null,
            "bank_account_id" => $bankAccount->getId(),
            "type" => Transaction::TYPE_EXPENSE,
            "description" => "Empréstimo — " . $loan->getDescription(),
            "amount" => $loan->getAmount(),
            "transaction_date" => date("Y-m-d"),
            "status" => Transaction::STATUS_CONFIRMED,
        ]);
        $transaction->save();
        $transaction->applyBalanceEffect();

        $loan->setTransactionId($transaction->getId());
        $loan->save();
    }
}