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
use App\Models\CardInvoicePayment;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\TransactionSplit;

class CardInvoiceController extends Controller
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
            $cards = CreditCard::findAllForUser($userId);

            $selectedCardId = !empty($_GET["cartao"]) ? (int)$_GET["cartao"] : ($cards[0]->getId() ?? null);
            $showAll = !empty($_GET["tudo"]);

            $window = ["past" => [], "current" => null, "future" => []];
            $allInvoices = [];

            if ($selectedCardId) {
                $card = CreditCard::findByIdForUser($selectedCardId, $userId);

                if ($card) {
                    if ($showAll) {
                        $allInvoices = CardInvoice::findAllForCard($selectedCardId);
                    } else {
                        $window = CardInvoice::findWindowForCard($selectedCardId, 1, 2);
                    }
                }
            }

            echo $this->view->render("card_invoices/index", [
                "title" => "Faturas | " . APP_NAME,
                "active" => "faturas",
                "cards" => $cards,
                "window" => $window,
                "allInvoices" => $allInvoices,
                "showAll" => $showAll,
                "selectedCardId" => $selectedCardId,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar faturas", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar as faturas.");
            redirect("/dashboard");
        }
    }

    public function show(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $invoice = CardInvoice::findByIdForUser($id, $userId);

            if (!$invoice) {
                Message::error("Fatura não encontrada.");
                redirect("/faturas");
                return;
            }

            $card = CreditCard::find($invoice->getCreditCardId());
            $transactions = Transaction::findAllForInvoice($id);

            $personTotals = TransactionSplit::findTotalsForInvoice($id);
            $grossSplitSum = array_sum(array_column($personTotals, 'gross'));
            $myOwnGross = max(0, ($invoice->getTotalAmount() ?? 0) - $grossSplitSum);
            $selfPaid = CardInvoicePayment::getSelfPaidTotalForInvoice($id);
            $myOwnAmount = max(0, $myOwnGross - $selfPaid);

            $payments = CardInvoicePayment::findAllForInvoice($id);
            $accounts = array_values(array_filter(BankAccount::findAllForUser($userId), fn($a) => $a->isActive()));

            $cardUsers = $card ? \App\Models\CardUser::findAllForUser($userId) : [];
            $cardUsers = array_values(array_filter(
                $cardUsers,
                static fn($cu) => in_array($invoice->getCreditCardId(), $cu->getLinkedCardIds(), true)
            ));

            echo $this->view->render("card_invoices/show", [
                "title" => "Fatura | " . APP_NAME,
                "active" => "faturas",
                "invoice" => $invoice,
                "card" => $card,
                "transactions" => $transactions,
                "payments" => $payments,
                "accounts" => $accounts,
                "personTotals" => $personTotals,
                "myOwnAmount" => $myOwnAmount,
                "cardUsers" => $cardUsers,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar fatura", [
                "user_id" => $userId,
                "invoice_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a fatura.");
            redirect("/faturas");
        }
    }

    public function pay(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/faturas/{$id}");

        $connection = Connection::getInstance();

        try {
            $invoice = CardInvoice::findByIdForUser($id, $userId);

            if (!$invoice) {
                Message::error("Fatura não encontrada.");
                redirect("/faturas");
                return;
            }

            $amount = (float)str_replace(",", ".", (string)($data["amount"] ?? "0"));
            $remaining = $invoice->getRemainingAmount();

            if ($amount <= 0) {
                Message::error("O valor do pagamento deve ser maior que zero.");
                redirect("/faturas/{$id}");
                return;
            }

            if ($amount > $remaining) {
                Message::error("O valor do pagamento não pode ser maior que o saldo devedor da fatura.");
                redirect("/faturas/{$id}");
                return;
            }

            $source = $data["payment_source"] ?? "account";
            $bankAccountId = null;
            $bankAccount = null;

            if ($source === "account") {
                $bankAccountId = !empty($data["bank_account_id"]) ? (int)$data["bank_account_id"] : null;

                if (!$bankAccountId) {
                    Message::error("Selecione a conta de onde o pagamento vai sair.");
                    redirect("/faturas/{$id}");
                    return;
                }

                $bankAccount = BankAccount::findByIdForUser($bankAccountId, $userId);

                if (!$bankAccount) {
                    Message::error("Conta bancária inválida.");
                    redirect("/faturas/{$id}");
                    return;
                }

                if (!$bankAccount->isActive()) {
                    Message::error("Essa conta está inativa.");
                    redirect("/faturas/{$id}");
                    return;
                }

                if ($amount > $bankAccount->getCurrentBalance()) {
                    Message::error(
                        "Saldo insuficiente na conta selecionada. Saldo disponível: R$ " .
                        number_format($bankAccount->getCurrentBalance(), 2, ',', '.') . "."
                    );
                    redirect("/faturas/{$id}");
                    return;
                }
            }

            $card = CreditCard::find($invoice->getCreditCardId());

            $payingCardUserId = !empty($data["paying_card_user_id"]) ? (int)$data["paying_card_user_id"] : null;

            if ($payingCardUserId) {
                $payingPerson = \App\Models\CardUser::findByIdForUser($payingCardUserId, $userId);
                if (!$payingPerson || !in_array($invoice->getCreditCardId(), $payingPerson->getLinkedCardIds(), true)) {
                    Message::error("A pessoa selecionada não está vinculada a esse cartão.");
                    redirect("/faturas/{$id}");
                    return;
                }
            }

            $paymentDate = $data["payment_date"] ?? date("Y-m-d");

            $connection->beginTransaction();

            try {
                $payment = new CardInvoicePayment();

                $payment->fill([
                    "card_invoice_id" => $id,
                    "bank_account_id" => $bankAccountId,
                    "paying_card_user_id" => $payingCardUserId,
                    "amount" => $amount,
                    "payment_date" => $paymentDate,
                    "notes" => $data["notes"] ?? null,
                ]);
                $payment->save();

                if ($bankAccount) {
                    $paymentTransaction = new Transaction();
                    $paymentTransaction->fill([
                        "user_id" => $userId,
                        "category_id" => null,
                        "bank_account_id" => $bankAccountId,
                        "type" => Transaction::TYPE_EXPENSE,
                        "description" => "Pagamento fatura " . ($card?->getName() ?? "cartão"),
                        "amount" => $amount,
                        "transaction_date" => $paymentDate,
                        "status" => Transaction::STATUS_CONFIRMED,
                    ]);
                    $paymentTransaction->save();
                    $paymentTransaction->applyBalanceEffect();
                }

                $newRemaining = $invoice->getRemainingAmount();

                if ($newRemaining <= 0.001) {
                    $invoice->setStatus(CardInvoice::STATUS_PAID);
                    $invoice->save();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::INVOICE_PAYMENT_CREATED, $userId, [
                "invoice_id" => $id,
                "payment_amount" => $amount,
                "source" => $source,
            ]);

            Message::success("Pagamento registrado com sucesso.");
            redirect("/faturas/{$id}");
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/faturas/{$id}");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao registrar pagamento de fatura", [
                "user_id" => $userId,
                "invoice_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível registrar o pagamento. Tente novamente.");
            redirect("/faturas/{$id}");
        }
    }
}