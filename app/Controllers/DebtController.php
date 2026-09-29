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
use App\Models\CreditCard;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;

class DebtController extends Controller
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

            echo $this->view->render("debts/index", [
                "title" => "Fiado | " . APP_NAME,
                "active" => "fiado",
                "debts" => Debt::findAllForUser($userId),
                "accounts" => array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive())),
                "cards" => array_values(array_filter(CreditCard::findAllForUser($userId), fn($c) => $c->isActive())),
                "cardUsers" => CardUser::findAllForUser($userId),
            ]);

        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar dívidas", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seus fiados.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        $userId = Auth::user()->id;

        echo $this->view->render("debts/create", [
            "title" => "Nova Dívida | " . APP_NAME,
            "active" => "fiado",
            "cardUsers" => CardUser::findAllForUser($userId),
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/fiado/novo");

        $userId = Auth::user()->id;

        try {
            $cardUser = CardUser::findByIdForUser((int)($data["card_user_id"] ?? 0), $userId);

            if (!$cardUser) {
                flash_old($data);
                Message::error("Pessoa inválida.");
                redirect("/fiado/novo");
                return;
            }

            $debt = new Debt();
            $debt->fill([
                "owner_user_id" => $userId,
                "card_user_id" => $cardUser->getId(),
                "description" => trim($data["description"] ?? ""),
                "amount" => $data["amount"] ?? "0",
                "due_date" => !empty($data["due_date"]) ? $data["due_date"] : null,
                "notes" => $data["notes"] ?? null,
                "status" => Debt::STATUS_OPEN,
            ]);

            $errors = $debt->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/fiado/novo");
                return;
            }

            $debt->save();

            AuditLog::record(LogEvent::DEBT_CREATED, $userId, [
                "debt_id" => $debt->getId(),
                "amount" => $debt->getAmount(),
            ]);

            clear_old();

            Message::success("Dívida registrada com sucesso.");
            redirect("/fiado");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/fiado/novo");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar dívida", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível registrar a dívida. Tente novamente.");
            redirect("/fiado/novo");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $debt = Debt::findByIdForUser($id, $userId);

            if (!$debt) {
                Message::error("Dívida não encontrada.");
                redirect("/fiado");
                return;
            }

            echo $this->view->render("debts/edit", [
                "title" => "Editar Dívida | " . APP_NAME,
                "active" => "fiado",
                "debt" => $debt,
                "cardUsers" => CardUser::findAllForUser($userId),
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar dívida para edição", [
                "user_id" => $userId,
                "debt_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a dívida.");
            redirect("/fiado");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/fiado/{$id}/editar");

        try {
            $debt = Debt::findByIdForUser($id, $userId);

            if (!$debt) {
                Message::error("Dívida não encontrada.");
                redirect("/fiado");
                return;
            }

            if ($debt->isPaid()) {
                Message::error("Essa dívida já foi paga e não pode ser editada.");
                redirect("/fiado");
                return;
            }

            $newAmount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

            if ($newAmount < $debt->getPaidAmount()) {
                Message::error(
                    "O valor não pode ser menor que o total já pago (R$ " .
                    number_format($debt->getPaidAmount(), 2, ',', '.') . ")."
                );
                redirect("/fiado/{$id}/editar");
                return;
            }

            $debt->fill([
                "card_user_id" => $data["card_user_id"] ?? $debt->getCardUserId(),
                "description" => trim($data["description"] ?? ""),
                "amount" => $newAmount,
                "due_date" => !empty($data["due_date"]) ? $data["due_date"] : null,
                "notes" => $data["notes"] ?? null,
            ]);

            $errors = $debt->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/fiado/{$id}/editar");
                return;
            }

            $debt->save();

            AuditLog::record(LogEvent::DEBT_UPDATED, $userId, ["debt_id" => $debt->getId()]);

            clear_old();

            Message::success("Dívida atualizada com sucesso.");
            redirect("/fiado");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/fiado/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar dívida", [
                "user_id" => $userId,
                "debt_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar a dívida. Tente novamente.");
            redirect("/fiado/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/fiado");

        try {
            $debt = Debt::findByIdForUser($id, $userId);

            if (!$debt) {
                Message::error("Dívida não encontrada.");
                redirect("/fiado");
                return;
            }

            if ($debt->getPaidAmount() > 0) {
                Message::error("Essa dívida já tem pagamentos registrados. Exclua os pagamentos antes.");
                redirect("/fiado");
                return;
            }

            $debt->delete();

            AuditLog::record(LogEvent::DEBT_DELETED, $userId, ["debt_id" => $id]);

            Message::success("Dívida excluída com sucesso.");
            redirect("/fiado");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir dívida", [
                "user_id" => $userId,
                "debt_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a dívida. Tente novamente.");
            redirect("/fiado");
        }
    }

    public function storePayment(?array $data): void
    {
        $userId = Auth::user()->id;
        $debtId = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/fiado/{$debtId}");

        $connection = Connection::getInstance();

        try {
            $debt = Debt::findByIdForUser($debtId, $userId);

            if (!$debt) {
                Message::error("Dívida não encontrada.");
                redirect("/fiado");
                return;
            }

            if ($debt->isPaid()) {
                Message::error("Essa dívida já está quitada.");
                redirect("/fiado");
                return;
            }

            $amount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));

            if ($amount <= 0) {
                Message::error("O valor do pagamento deve ser maior que zero.");
                redirect("/fiado");
                return;
            }

            if ($amount > $debt->getRemainingAmount() + 0.001) {
                Message::error(
                    "O valor não pode ultrapassar o saldo em aberto (R$ " .
                    number_format($debt->getRemainingAmount(), 2, ',', '.') . ")."
                );
                redirect("/fiado");
                return;
            }

            $paymentDate = $data["payment_date"] ?? date("Y-m-d");

            if (!\DateTime::createFromFormat("Y-m-d", $paymentDate)) {
                Message::error("Data de pagamento inválida.");
                redirect("/fiado");
                return;
            }

            if ($paymentDate > date("Y-m-d")) {
                Message::error("A data do pagamento não pode ser no futuro.");
                redirect("/fiado");
                return;
            }

            $source = $data["payment_source"] ?? "account";
            $bankAccount = null;
            $creditCard = null;
            $payingCardUserId = null;

            if ($source === "account") {
                $bankAccount = BankAccount::findByIdForUser((int)($data["bank_account_id"] ?? 0), $userId);

                if (!$bankAccount) {
                    Message::error("Conta bancária inválida.");
                    redirect("/fiado");
                    return;
                }

                if (!$bankAccount->isActive()) {
                    Message::error("Essa conta está inativa.");
                    redirect("/fiado");
                    return;
                }

                if ($amount > $bankAccount->getCurrentBalance()) {
                    Message::error(
                        "Saldo insuficiente na conta selecionada. Saldo disponível: R$ " .
                        number_format($bankAccount->getCurrentBalance(), 2, ',', '.') . "."
                    );
                    redirect("/fiado");
                    return;
                }
            } elseif ($source === "card") {
                $creditCard = CreditCard::findByIdForUser((int)($data["credit_card_id"] ?? 0), $userId);

                if (!$creditCard) {
                    Message::error("Cartão inválido.");
                    redirect("/fiado");
                    return;
                }

                if (!$creditCard->isActive()) {
                    Message::error("Esse cartão está inativo.");
                    redirect("/fiado");
                    return;
                }

                if ($amount > $creditCard->getAvailableLimit()) {
                    Message::error(
                        "Esse pagamento ultrapassa o limite disponível do cartão. Disponível: R$ " .
                        number_format($creditCard->getAvailableLimit(), 2, ',', '.') . "."
                    );
                    redirect("/fiado");
                    return;
                }
            } else {
                $payingCardUser = CardUser::findByIdForUser((int)($data["paying_person_id"] ?? 0), $userId);

                if (!$payingCardUser) {
                    Message::error("Pessoa inválida.");
                    redirect("/fiado");
                    return;
                }

                $payingCardUserId = $payingCardUser->getId();
            }

            $connection->beginTransaction();

            try {
                $invoice = null;

                if ($creditCard) {
                    $invoice = $creditCard->resolveInvoiceForDate($paymentDate);
                    $invoice->closeIfDue();

                    if ($invoice->getStatus() === CardInvoice::STATUS_CLOSED) {
                        $connection->rollBack();
                        Message::error("Essa fatura já fechou. Não é possível lançar o pagamento nela.");
                        redirect("/fiado");
                        return;
                    }
                }

                $payment = new DebtPayment();
                $payment->fill([
                    "debt_id" => $debt->getId(),
                    "bank_account_id" => $bankAccount?->getId(),
                    "credit_card_id" => $creditCard?->getId(),
                    "paying_card_user_id" => $payingCardUserId,
                    "amount" => $amount,
                    "payment_date" => $paymentDate,
                    "notes" => $data["notes"] ?? null,
                ]);
                $payment->save();

                if ($bankAccount) {
                    $expenseTransaction = new Transaction();
                    $expenseTransaction->fill([
                        "user_id" => $userId,
                        "category_id" => null,
                        "bank_account_id" => $bankAccount->getId(),
                        "type" => Transaction::TYPE_EXPENSE,
                        "description" => "Pagamento fiado — " . $debt->getDescription(),
                        "amount" => $amount,
                        "transaction_date" => $paymentDate,
                        "status" => Transaction::STATUS_CONFIRMED,
                    ]);
                    $expenseTransaction->save();
                    $expenseTransaction->applyBalanceEffect();

                    $payment->setTransactionId($expenseTransaction->getId());
                    $payment->save();
                } elseif ($creditCard) {
                    $expenseTransaction = new Transaction();
                    $expenseTransaction->fill([
                        "user_id" => $userId,
                        "category_id" => null,
                        "credit_card_id" => $creditCard->getId(),
                        "card_invoice_id" => $invoice->getId(),
                        "type" => Transaction::TYPE_EXPENSE,
                        "description" => "Pagamento fiado — " . $debt->getDescription(),
                        "amount" => $amount,
                        "transaction_date" => $paymentDate,
                        "status" => Transaction::STATUS_CONFIRMED,
                    ]);
                    $expenseTransaction->save();
                    $invoice->recalculateTotal();

                    $payment->setTransactionId($expenseTransaction->getId());
                    $payment->save();
                }

                if ($amount >= $debt->getRemainingAmount() - 0.001) {
                    $debt->setStatus(Debt::STATUS_PAID);
                    $debt->save();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::DEBT_PAYMENT_CREATED, $userId, [
                "debt_id" => $debt->getId(),
                "amount" => $amount,
                "source" => $source,
            ]);

            Message::success("Pagamento registrado com sucesso.");
            redirect("/fiado");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao registrar pagamento de dívida", [
                "user_id" => $userId,
                "debt_id" => $debtId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível registrar o pagamento. Tente novamente.");
            redirect("/fiado");
        }
    }

    public function destroyPayment(?array $data): void
    {
        $userId = Auth::user()->id;
        $debtId = (int)($data["id"] ?? 0);
        $paymentId = (int)($data["paymentId"] ?? 0);

        $this->validateCsrfToken($data, "/fiado");

        $connection = Connection::getInstance();

        try {
            $debt = Debt::findByIdForUser($debtId, $userId);
            $payment = DebtPayment::findByIdForUser($paymentId, $userId);

            if (!$debt || !$payment || $payment->getDebtId() !== $debt->getId()) {
                Message::error("Pagamento não encontrado.");
                redirect("/fiado");
                return;
            }

            $connection->beginTransaction();

            try {
                $transaction = $payment->getTransactionId() ? Transaction::find($payment->getTransactionId()) : null;
                $invoiceId = $transaction?->getCardInvoiceId();

                if ($transaction) {
                    $transaction->reverseBalanceEffect();
                    $transaction->delete();
                }

                if ($invoiceId) {
                    CardInvoice::find($invoiceId)?->recalculateTotal();
                }

                $payment->delete();

                if ($debt->isPaid()) {
                    $debt->setStatus(Debt::STATUS_OPEN);
                    $debt->save();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::DEBT_PAYMENT_DELETED, $userId, [
                "debt_id" => $debtId,
                "payment_id" => $paymentId,
            ]);

            Message::success("Pagamento excluído com sucesso.");
            redirect("/fiado");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir pagamento de dívida", [
                "user_id" => $userId,
                "debt_id" => $debtId,
                "payment_id" => $paymentId,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir o pagamento. Tente novamente.");
            redirect("/fiado");
        }
    }
}