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

            // Explícito e amigável — não deixa nem chegar no Model,
            // pra dar uma mensagem clara em vez do popup do navegador.
            $installmentsCount = (int)($data["installments_count"] ?? 0);

            if ($installmentsCount < 2) {
                flash_old($data);
                Message::error("Uma compra parcelada precisa ter no mínimo 2 parcelas. Se for pagamento único, use um Lançamento normal.");
                redirect("/parcelamentos/novo");
                return;
            }

            $purchaseDate = $data["purchase_date"] ?? date("Y-m-d");

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

            $errors = $purchase->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/parcelamentos/novo");
                return;
            }

            $splitsResult = $this->validateSplits($data, $userId, $creditCardId, $purchase->getTotalAmount());

            if (is_string($splitsResult)) {
                flash_old($data);
                Message::error($splitsResult);
                redirect("/parcelamentos/novo");
                return;
            }

            // Descobre o mês de referência da 1ª parcela a partir da
            // DATA DA COMPRA — se comprou antes do fechamento, cai na
            // fatura atual; se depois, cai na próxima. A data real da
            // 1ª parcela é o VENCIMENTO dessa fatura, não a data digitada.
            $firstReferenceMonth = $card->getReferenceMonthForPurchase($purchaseDate);
            $firstInvoice = $card->resolveInvoiceForReferenceMonth($firstReferenceMonth);
            $purchase->setFirstInstallmentDate($firstInvoice->getDueDate());

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

    private function validateSplits(array $data, int $userId, int $creditCardId, float $totalAmount): array|string
    {
        $personIds = $data["split_card_user_id"] ?? [];
        $amounts = $data["split_amount"] ?? [];

        if (empty($personIds)) {
            return [];
        }

        $splits = [];
        $seenPersonIds = [];
        $sum = 0.0;

        foreach ($personIds as $index => $personId) {
            $personId = (int)$personId;
            $amount = (float)str_replace(",", ".", (string)($amounts[$index] ?? "0"));

            if (!$personId || $amount <= 0) {
                continue;
            }

            if (in_array($personId, $seenPersonIds, true)) {
                return "Você adicionou a mesma pessoa mais de uma vez na divisão.";
            }

            $cardUser = CardUser::findByIdForUser($personId, $userId);

            if (!$cardUser || !in_array($creditCardId, $cardUser->getLinkedCardIds(), true)) {
                return "Uma das pessoas selecionadas não está vinculada a esse cartão.";
            }

            $seenPersonIds[] = $personId;
            $sum += $amount;
            $splits[] = ["card_user_id" => $personId, "amount" => $amount];
        }

        if ($sum > $totalAmount) {
            return "A soma da divisão não pode ultrapassar o valor total da compra.";
        }

        return $splits;
    }
}