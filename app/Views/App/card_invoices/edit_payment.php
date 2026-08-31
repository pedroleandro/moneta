<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Editar Pagamento | " . APP_NAME,
        "active" => "faturas",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Pagamento</h4>
    <?= \App\Core\Message::render() ?>
    <div class="card">
        <div class="card-body">
            <form action="<?= url('/faturas/' . $invoice->getId() . '/pagamentos/' . $payment->getId() . '/editar') ?>"
                  method="post" class="needs-validation">
                <?= csrf_input() ?>
                <div class="mb-6">
                    <label for="amount_display" class="form-label">Valor</label>
                    <input type="text" class="form-control currency-mask" id="amount_display"
                           data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                    <input type="hidden" id="amount" name="amount"
                           value="<?= number_format($payment->getAmount(), 2, '.', '') ?>"/>
                </div>
                <div class="mb-6">
                    <label for="payment_date" class="form-label">Data do Pagamento</label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                           value="<?= htmlspecialchars($payment->getPaymentDate()) ?>" required/>
                </div>
                <div class="mb-6">
                    <label for="paying_person_id" class="form-label">Quem pagou <small class="text-body-secondary">(opcional)</small></label>
                    <select class="form-select" id="paying_person_id" name="paying_person_id">
                        <option value="">Eu mesmo</option>
                        <?php foreach ($cardUsers as $cardUser): ?>
                            <option value="<?= $cardUser->getId() ?>"
                                    <?= (string)$payment->getPayingCardUserId() === (string)$cardUser->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cardUser->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-body-secondary">
                        Se for uma das pessoas que dividem esse cartão, o valor pago por ela abate do que ela deve.
                    </small>
                </div>
                <div class="mb-6">
                    <label for="notes" class="form-label">Observações <small
                                class="text-body-secondary">(opcional)</small></label>
                    <input type="text" class="form-control" id="notes" name="notes"
                           value="<?= htmlspecialchars($payment->getNotes() ?? '') ?>"/>
                </div>
                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/faturas/' . $invoice->getId()) ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
