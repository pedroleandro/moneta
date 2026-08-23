<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Fatura | " . APP_NAME,
        "active" => "faturas",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">
            Fatura <?= htmlspecialchars($card?->getName() ?? '') ?> —
            <?= date('m/Y', strtotime($invoice->getReferenceMonth())) ?>
        </h4>
        <a href="<?= url('/faturas?cartao=' . $invoice->getCreditCardId()) ?>" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="row mb-6">
        <div class="col-6 col-md-3 mb-4 mb-md-0">
            <div class="card">
                <div class="card-body">
                    <small class="text-body-secondary d-block">Total da Fatura</small>
                    <h5 class="mb-0">R$ <?= number_format($invoice->getTotalAmount() ?? 0, 2, ',', '.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-4 mb-md-0">
            <div class="card">
                <div class="card-body">
                    <small class="text-body-secondary d-block">Já Pago</small>
                    <h5 class="mb-0 text-success">R$ <?= number_format($invoice->getPaidAmount(), 2, ',', '.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-body-secondary d-block">Falta Pagar</small>
                    <h5 class="mb-0 text-danger">
                        R$ <?= number_format($invoice->getRemainingAmount(), 2, ',', '.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-body-secondary d-block">Vencimento</small>
                    <h5 class="mb-0"><?= date('d/m/Y', strtotime($invoice->getDueDate())) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-6">
                <div class="card-header">
                    <h6 class="mb-0">Lançamentos dessa fatura</h6>
                </div>
                <div class="table-responsive table-responsive-mobile text-nowrap">
                    <table class="table table-datatable">
                        <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th class="text-end">Valor</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr class="table-empty-row">
                                <td colspan="4" class="text-center py-6">Nenhum lançamento nessa fatura.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td data-label="Data">
                                    <?= date('d/m/Y', strtotime($transaction->getTransactionDate())) ?>
                                </td>
                                <td data-label="Descrição">
                                    <?= htmlspecialchars($transaction->getDescription()) ?>
                                </td>
                                <td data-label="Categoria">
                                    <?= htmlspecialchars($transaction->getCategoryName() ?? '-') ?>
                                </td>
                                <td data-label="Valor" class="text-end">
                                    R$ <?= number_format($transaction->getAmount(), 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-6">
                <div class="card-header">
                    <h6 class="mb-0">Quanto cada pessoa deve nessa fatura</h6>
                </div>
                <div class="table-responsive table-responsive-mobile text-nowrap">
                    <table class="table table-datatable">
                        <thead>
                        <tr>
                            <th>Pessoa</th>
                            <th class="text-end">Valor</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($personTotals) && $myOwnAmount <= 0): ?>
                            <tr class="table-empty-row">
                                <td colspan="2" class="text-center py-6">Nenhuma divisão registrada nessa fatura.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($personTotals as $personTotal): ?>
                            <tr>
                                <td data-label="Pessoa"><?= htmlspecialchars($personTotal['card_user_name']) ?></td>
                                <td data-label="Valor" class="text-end">
                                    R$ <?= number_format((float)$personTotal['total'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($myOwnAmount > 0): ?>
                            <tr class="fw-bold">
                                <td data-label="Pessoa">Meu (não dividido)</td>
                                <td data-label="Valor" class="text-end">
                                    R$ <?= number_format($myOwnAmount, 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Pagamentos registrados</h6>
                </div>
                <div class="table-responsive table-responsive-mobile text-nowrap">
                    <table class="table table-datatable">
                        <thead>
                        <tr>
                            <th>Data</th>
                            <th>Origem</th>
                            <th class="text-end">Valor</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($payments)): ?>
                            <tr class="table-empty-row">
                                <td colspan="3" class="text-center py-6">Nenhum pagamento registrado ainda.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td data-label="Data">
                                    <?= date('d/m/Y', strtotime($payment->getPaymentDate())) ?>
                                </td>
                                <td data-label="Origem">
                                    <?= htmlspecialchars($payment->getBankAccountName() ?? 'Outro / Dinheiro') ?>
                                </td>
                                <td data-label="Valor" class="text-end">
                                    R$ <?= number_format($payment->getAmount(), 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <?php if ($invoice->getRemainingAmount() > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Registrar Pagamento</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/faturas/' . $invoice->getId() . '/pagar') ?>" method="post">
                            <?= csrf_input() ?>

                            <div class="mb-6">
                                <label for="amount_display" class="form-label">Valor</label>
                                <input type="text" class="form-control currency-mask" id="amount_display"
                                       data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                                <input type="hidden" id="amount" name="amount"
                                       value="<?= number_format($invoice->getRemainingAmount(), 2, '.', '') ?>"/>
                                <small class="text-body-secondary">
                                    Saldo devedor: R$ <?= number_format($invoice->getRemainingAmount(), 2, ',', '.') ?>
                                </small>
                            </div>

                            <div class="mb-6">
                                <label for="payment_date" class="form-label">Data do Pagamento</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date"
                                       value="<?= date('Y-m-d') ?>" required/>
                            </div>

                            <div class="mb-6">
                                <label class="form-label d-block">Origem</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_source"
                                           id="src_account" value="account" checked>
                                    <label class="form-check-label" for="src_account">Minha conta bancária</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_source"
                                           id="src_other" value="other">
                                    <label class="form-check-label" for="src_other">
                                        Outro / Dinheiro (não afeta nenhuma conta)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-6" id="wrapper-bank-account">
                                <label for="bank_account_id" class="form-label">Conta</label>
                                <select class="form-select" id="bank_account_id" name="bank_account_id">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account->getId() ?>">
                                            <?= htmlspecialchars($account->getName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label for="notes" class="form-label">Observações <small class="text-body-secondary">(opcional)</small></label>
                                <input type="text" class="form-control" id="notes" name="notes"/>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">Registrar Pagamento</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">Essa fatura já está totalmente paga.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const srcAccount = document.getElementById('src_account');
        const srcOther = document.getElementById('src_other');
        const wrapper = document.getElementById('wrapper-bank-account');

        function toggle() {
            if (!wrapper) return;
            wrapper.style.display = srcOther && srcOther.checked ? 'none' : '';
        }

        if (srcAccount) srcAccount.addEventListener('change', toggle);
        if (srcOther) srcOther.addEventListener('change', toggle);
    });
</script>