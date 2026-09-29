<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Editar Empréstimo | " . APP_NAME,
    "active" => "emprestimos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Empréstimo</h4>

    <?= \App\Core\Message::render() ?>

    <?php if ($loan->getReturnedAmount() > 0): ?>
        <div class="alert alert-warning">
            Esse empréstimo já tem R$ <?= number_format($loan->getReturnedAmount(), 2, ',', '.') ?> devolvido —
            o valor não pode ser reduzido abaixo disso.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/emprestimos/' . $loan->getId() . '/editar') ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="card_user_id" class="form-label">Pessoa</label>
                        <select class="form-select" id="card_user_id" name="card_user_id" required>
                            <?php foreach ($cardUsers as $cardUser): ?>
                                <option value="<?= $cardUser->getId() ?>"
                                    <?= (string)old('card_user_id', (string)$loan->getCardUserId()) === (string)$cardUser->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cardUser->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <?php if ($loan->getReturnedAmount() > 0): ?>
                            <input type="text" class="form-control" value="R$ <?= number_format($loan->getAmount(), 2, ',', '.') ?>" disabled/>
                            <input type="hidden" name="amount" value="<?= $loan->getAmount() ?>"/>
                            <small class="text-body-secondary">Já tem devolução registrada — o valor não pode mais ser alterado.</small>
                        <?php else: ?>
                            <input type="text" class="form-control currency-mask" id="amount_display"
                                   data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                            <input type="hidden" id="amount" name="amount" value="<?= old('amount', $loan->getAmount()) ?>"/>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= old('description', $loan->getDescription()) ?>" required/>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="due_date" class="form-label">Data de retorno <small class="text-body-secondary">(opcional)</small></label>
                        <input type="date" class="form-control" id="due_date" name="due_date"
                               value="<?= old('due_date', $loan->getDueDate()) ?>"/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required
                            <?= $loan->isConfirmed() ? 'disabled' : '' ?>>
                            <option value="pendente" <?= old('status', $loan->getStatus()) === 'pendente' ? 'selected' : '' ?>>
                                Pendente
                            </option>
                            <option value="confirmado" <?= old('status', $loan->getStatus()) === 'confirmado' ? 'selected' : '' ?>>
                                Já emprestei
                            </option>
                        </select>
                        <?php if ($loan->isConfirmed()): ?>
                            <input type="hidden" name="status" value="confirmado"/>
                            <small class="text-body-secondary">Já confirmado — não é possível voltar para pendente.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row" id="wrapper-account"
                     style="<?= in_array(old('status', $loan->getStatus()), ['confirmado'], true) ? '' : 'display:none;' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="bank_account_id" class="form-label">Conta de origem</label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account->getId() ?>"
                                    <?= (string)old('bank_account_id', (string)$loan->getBankAccountId()) === (string)$account->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Observações <small class="text-body-secondary">(opcional)</small></label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"><?= old('notes', $loan->getNotes()) ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="<?= url('/emprestimos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('status').addEventListener('change', function () {
        document.getElementById('wrapper-account').style.display = this.value === 'confirmado' ? '' : 'none';
    });
</script>
