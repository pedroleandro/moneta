<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Connection;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\CardInvoice;
use App\Models\CardUser;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\InstallmentPurchase;
use App\Models\Transaction;
use App\Models\TransactionSplit;

class InstallmentPurchaseController extends Controller
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
            $purchases = InstallmentPurchase::findAllForUser($userId);

            echo $this->view->render("installment_purchases/index", [
                "title" => "Compras Parceladas | " . APP_NAME,
                "active" => "lancamentos-parcelamentos",
                "purchases" => $purchases,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar parcelamentos", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar os parcelamentos.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        try {
            $userId = Auth::user()->id;

            $cards = array_values(array_filter(CreditCard::findAllForUser($userId), fn($c) => $c->isActive()));
            $categories = array_values(array_filter(
                Category::findAllForUser($userId),
                fn($c) => $c->getType() === "despesa"
            ));
            $cardUsers = CardUser::findAllForUser($userId);

            if (empty($cards)) {
                Message::warning("Cadastre um cartão de crédito ativo antes de parcelar uma compra.");
                redirect("/cartoes/novo");
                return;
            }

            echo $this->view->render("installment_purchases/create", [
                "title" => "Nova Compra Parcelada | " . APP_NAME,
                "active" => "lancamentos-parcelamentos",
                "cards" => $cards,
                "categories" => $categories,
                "cardUsers" => $cardUsers,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar formulário de parcelamento", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o formulário.");
            redirect("/parcelamentos");
        }
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/parcelamentos/novo");

        $userId = Auth::user()->id;
        $connection = Connection::getInstance();

        try {
            $creditCardId = (int)($data["credit_card_id"] ?? 0);
            $card = CreditCard::findByIdForUser($creditCardId, $userId);

            if (!$card) {
                flash_old($data);
                Message::error("Cartão inválido.");
                redirect("/parcelamentos/novo");
                return;
            }

            if (!$card->isActive()) {
                flash_old($data);
                Message::error("Esse cartão está inativo.");
                redirect("/parcelamentos/novo");
                return;
            }

            $categoryId = (int)($data["category_id"] ?? 0);
            $category = Category::findByIdForUser($categoryId, $userId);

            if (!$category || $category->getType() !== "despesa") {
                flash_old($data);
                Message::error("Categoria inválida — parcelamento sempre é despesa.");
                redirect("/parcelamentos/novo");
                return;
            }

            $installmentsCount = (int)($data["installments_count"] ?? 0);

            if ($installmentsCount < 2) {
                flash_old($data);
                Message::error("Uma compra parcelada precisa ter no mínimo 2 parcelas. Se for pagamento único, use um Lançamento normal.");
                redirect("/parcelamentos/novo");
                return;
            }

            $purchaseDate = $data["purchase_date"] ?? date("Y-m-d");

            $dateCheck = $this->validatePurchaseDate($purchaseDate, $card);
            if (is_string($dateCheck)) {
                flash_old($data);
                Message::error($dateCheck);
                redirect("/parcelamentos/novo");
                return;
            }

            $purchase = new InstallmentPurchase();
            $purchase->fill([
                "user_id" => $userId,
                "credit_card_id" => $creditCardId,
                "category_id" => $categoryId,
                "description" => trim($data["description"] ?? ""),
                "total_amount" => $data["total_amount"] ?? "0",
                "installments_count" => $installmentsCount,
                // Placeholder só pra passar pela validação de formato de
                // data — o valor real (vencimento da 1ª fatura) é
                // calculado logo abaixo, antes de salvar de verdade.
                "first_installment_date" => $purchaseDate,
            ]);

            $data["first_installment_date"] = $purchaseDate;
            $errors = $purchase->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/parcelamentos/novo");
                return;
            }

            $splitsResult = CardUser::validateSplitAssignments(
                $data["split_card_user_id"] ?? [],
                $data["split_amount"] ?? [],
                $userId,
                $creditCardId,
                $purchase->getTotalAmount()
            );

            if (is_string($splitsResult)) {
                flash_old($data);
                Message::error($splitsResult);
                redirect("/parcelamentos/novo");
                return;
            }

            $firstReferenceMonth = $card->getReferenceMonthForPurchase($purchaseDate);
            $firstInvoice = $card->resolveInvoiceForReferenceMonth($firstReferenceMonth);

            if ($firstInvoice->getStatus() === \App\Models\CardInvoice::STATUS_PAID) {
                flash_old($data);
                Message::error("Essa data cai numa fatura que já foi paga. Escolha uma data mais recente.");
                redirect("/parcelamentos/novo");
                return;
            }

            $purchase->setFirstInstallmentDate($purchaseDate);

            $connection->beginTransaction();

            try {
                $purchase->save();

                $amounts = $purchase->calculateInstallmentAmounts();
                $affectedInvoiceIds = [$firstInvoice->getId() => true];

                foreach ($amounts as $index => $installmentAmount) {
                    $referenceMonth = (new \DateTimeImmutable($firstReferenceMonth))
                        ->modify("+{$index} month")
                        ->format('Y-m-01');

                    $invoice = $card->resolveInvoiceForReferenceMonth($referenceMonth);
                    $installmentDate = $invoice->getDueDate();
                    $affectedInvoiceIds[$invoice->getId()] = true;

                    $transaction = new Transaction();
                    $transaction->fill([
                        "user_id" => $userId,
                        "category_id" => $categoryId,
                        "credit_card_id" => $creditCardId,
                        "card_invoice_id" => $invoice->getId(),
                        "installment_purchase_id" => $purchase->getId(),
                        "installment_number" => $index + 1,
                        "type" => Transaction::TYPE_EXPENSE,
                        "description" => $purchase->getDescription() . " (" . ($index + 1) . "/" . count($amounts) . ")",
                        "amount" => $installmentAmount,
                        "transaction_date" => $installmentDate,
                        "status" => Transaction::STATUS_CONFIRMED,
                    ]);
                    $transaction->save();

                    foreach ($splitsResult as $split) {
                        $proportionalAmount = round(
                            $installmentAmount * ($split["amount"] / $purchase->getTotalAmount()),
                            2
                        );

                        if ($proportionalAmount <= 0) {
                            continue;
                        }

                        $transactionSplit = new TransactionSplit();
                        $transactionSplit->fill([
                            "transaction_id" => $transaction->getId(),
                            "card_user_id" => $split["card_user_id"],
                            "amount" => $proportionalAmount,
                        ]);
                        $transactionSplit->save();
                    }
                }

                foreach (array_keys($affectedInvoiceIds) as $invoiceId) {
                    $invoice = CardInvoice::find($invoiceId);
                    $invoice?->recalculateTotal();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::INSTALLMENT_PURCHASE_CREATED, $userId, [
                "installment_purchase_id" => $purchase->getId(),
                "total_amount" => $purchase->getTotalAmount(),
                "installments_count" => $purchase->getInstallmentsCount(),
            ]);

            clear_old();

            Message::success("Compra parcelada em " . $purchase->getInstallmentsCount() . "x criada com sucesso.");
            redirect("/parcelamentos");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/parcelamentos/novo");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar parcelamento", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar o parcelamento. Tente novamente.");
            redirect("/parcelamentos/novo");
        }
    }

    private function validatePurchaseDate(string $purchaseDate, CreditCard $card): true|string
    {
        $date = \DateTime::createFromFormat("Y-m-d", $purchaseDate);

        if (!$date) {
            return true;
        }

        $today = new \DateTime("today");
        $date->setTime(0, 0, 0);

        if ($date > $today) {
            return "A data da compra não pode ser no futuro.";
        }

        $earliest = $card->getEarliestAllowedDate();

        if ($purchaseDate < $earliest) {
            $earliestFormatted = date("d/m/Y", strtotime($earliest));
            return "A data da compra está muito no passado. A data mais antiga permitida é {$earliestFormatted}.";
        }

        return true;
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $purchase = InstallmentPurchase::findByIdForUser($id, $userId);

            if (!$purchase) {
                Message::error("Parcelamento não encontrado.");
                redirect("/parcelamentos");
                return;
            }

            if ($purchase->hasAnyInstallmentInPaidInvoice()) {
                Message::error("Uma ou mais parcelas já estão em fatura paga. O prazo pra editar ou cancelar esse parcelamento já passou.");
                redirect("/parcelamentos");
                return;
            }

            echo $this->view->render("installment_purchases/edit", [
                "title" => "Editar Parcelamento | " . APP_NAME,
                "active" => "lancamentos-parcelamentos",
                "purchase" => $purchase,
            ]);
            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar edição de parcelamento", [
                "user_id" => $userId,
                "installment_purchase_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar o parcelamento.");
            redirect("/parcelamentos");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/parcelamentos/{$id}/editar");

        $connection = Connection::getInstance();

        try {
            $purchase = InstallmentPurchase::findByIdForUser($id, $userId);

            if (!$purchase) {
                Message::error("Parcelamento não encontrado.");
                redirect("/parcelamentos");
                return;
            }

            if ($purchase->hasAnyInstallmentInPaidInvoice()) {
                Message::error("Uma ou mais parcelas já estão em fatura paga. O prazo pra editar esse parcelamento já passou.");
                redirect("/parcelamentos");
                return;
            }

            $newTotal = (float)str_replace(",", ".", (string)($data["total_amount"] ?? "0"));

            if ($newTotal <= 0) {
                flash_old($data);
                Message::error("O valor total deve ser maior que zero.");
                redirect("/parcelamentos/{$id}/editar");
                return;
            }

            $card = CreditCard::find($purchase->getCreditCardId());
            $oldTotal = $purchase->getTotalAmount();

            // Checa limite disponível do cartão se o valor for aumentar
            if ($card && $newTotal > $oldTotal) {
                $increase = $newTotal - $oldTotal;
                if ($increase > $card->getAvailableLimit()) {
                    flash_old($data);
                    Message::error("Limite insuficiente no cartão para aumentar o valor dessa compra.");
                    redirect("/parcelamentos/{$id}/editar");
                    return;
                }
            }

            $transactions = Transaction::findAllForInstallmentPurchase($purchase->getId());

            if (empty($transactions)) {
                Message::error("Não foram encontradas parcelas para esse parcelamento.");
                redirect("/parcelamentos");
                return;
            }

            $connection->beginTransaction();

            try {
                $purchase->fill(["total_amount" => $newTotal]);
                $purchase->save();

                $newAmounts = $purchase->calculateInstallmentAmounts();

                $affectedInvoiceIds = [];

                foreach ($transactions as $transaction) {
                    $installmentIndex = ($transaction->getInstallmentNumber() ?? 1) - 1;
                    $newInstallmentAmount = $newAmounts[$installmentIndex] ?? $transaction->getAmount();
                    $oldInstallmentAmount = $transaction->getAmount();

                    $transaction->fill(["amount" => $newInstallmentAmount]);
                    $transaction->save();

                    $affectedInvoiceIds[$transaction->getCardInvoiceId()] = true;

                    // Recalcula os splits proporcionalmente ao novo valor da parcela
                    $splits = TransactionSplit::findAllForTransaction($transaction->getId());

                    if (!empty($splits) && $oldInstallmentAmount > 0) {
                        TransactionSplit::deleteAllForTransaction($transaction->getId());

                        foreach ($splits as $split) {
                            $proportion = $split->getAmount() / $oldInstallmentAmount;
                            $newSplitAmount = round($newInstallmentAmount * $proportion, 2);

                            if ($newSplitAmount <= 0) {
                                continue;
                            }

                            $newSplit = new TransactionSplit();
                            $newSplit->fill([
                                "transaction_id" => $transaction->getId(),
                                "card_user_id" => $split->getCardUserId(),
                                "amount" => $newSplitAmount,
                            ]);
                            $newSplit->save();
                        }
                    }
                }

                foreach (array_keys($affectedInvoiceIds) as $invoiceId) {
                    $invoice = CardInvoice::find($invoiceId);
                    $invoice?->recalculateTotal();
                }

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::INSTALLMENT_PURCHASE_UPDATED, $userId, [
                "installment_purchase_id" => $purchase->getId(),
                "old_total" => $oldTotal,
                "new_total" => $newTotal,
            ]);

            clear_old();
            Message::success("Parcelamento atualizado com sucesso.");
            redirect("/parcelamentos");
        } catch (\InvalidArgumentException $exception) {
            Message::error($exception->getMessage());
            redirect("/parcelamentos/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao editar parcelamento", [
                "user_id" => $userId,
                "installment_purchase_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível editar o parcelamento. Tente novamente.");
            redirect("/parcelamentos/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/parcelamentos");

        $connection = Connection::getInstance();

        try {
            $purchase = InstallmentPurchase::findByIdForUser($id, $userId);

            if (!$purchase) {
                Message::error("Parcelamento não encontrado.");
                redirect("/parcelamentos");
                return;
            }

            if ($purchase->hasAnyInstallmentInPaidInvoice()) {
                Message::error("Uma ou mais parcelas já estão em fatura paga. O prazo pra cancelar esse parcelamento já passou.");
                redirect("/parcelamentos");
                return;
            }

            $transactions = Transaction::findAllForInstallmentPurchase($purchase->getId());

            $connection->beginTransaction();

            try {
                $affectedInvoiceIds = [];

                foreach ($transactions as $transaction) {
                    $affectedInvoiceIds[$transaction->getCardInvoiceId()] = true;
                    TransactionSplit::deleteAllForTransaction($transaction->getId());
                    $transaction->delete();
                }

                foreach (array_keys($affectedInvoiceIds) as $invoiceId) {
                    $invoice = CardInvoice::find($invoiceId);
                    $invoice?->recalculateTotal();
                }

                $purchase->delete();

                $connection->commit();
            } catch (\Throwable $inner) {
                $connection->rollBack();
                throw $inner;
            }

            AuditLog::record(LogEvent::INSTALLMENT_PURCHASE_CANCELED, $userId, [
                "installment_purchase_id" => $id,
            ]);

            Message::success("Parcelamento cancelado com sucesso.");
            redirect("/parcelamentos");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao cancelar parcelamento", [
                "user_id" => $userId,
                "installment_purchase_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível cancelar o parcelamento. Tente novamente.");
            redirect("/parcelamentos");
        }
    }
}