<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Novo Empréstimo | " . APP_NAME,
    "active" => "emprestimos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Novo Empréstimo</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/emprestimos/novo') ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="card_user_id" class="form-label">Pessoa</label>
                        <select class="form-select" id="card_user_id" name="card_user_id" required
                                data-error-required="Selecione a pessoa.">
                            <option value="" disabled <?= old('card_user_id') ? '' : 'selected' ?>>Selecione...</option>
                            <?php foreach ($cardUsers as $cardUser): ?>
                                <option value="<?= $cardUser->getId() ?>"
                                    <?= (string)old('card_user_id') === (string)$cardUser->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cardUser->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <input type="text" class="form-control currency-mask" id="amount_display"
                               data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required
                               data-error-required="Informe o valor."/>
                        <input type="hidden" id="amount" name="amount" value="<?= old('amount', '0.00') ?>"/>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= old('description') ?>" placeholder="Ex: Empréstimo para conserto do carro" required
                           data-error-required="Informe a descrição."/>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="due_date" class="form-label">Data de retorno <small class="text-body-secondary">(opcional)</small></label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="<?= old('due_date') ?>"/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pendente" <?= old('status', 'pendente') === 'pendente' ? 'selected' : '' ?>>
                                Pendente (só anotar, sem tirar da conta)
                            </option>
                            <option value="confirmado" <?= old('status') === 'confirmado' ? 'selected' : '' ?>>
                                Já emprestei (sai da conta agora)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row" id="wrapper-account" style="<?= old('status') === 'confirmado' ? '' : 'display:none;' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="bank_account_id" class="form-label">Conta de origem</label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account->getId() ?>"
                                    <?= (string)old('bank_account_id') === (string)$account->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Observações <small class="text-body-secondary">(opcional)</small></label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"><?= old('notes') ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/emprestimos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('status').addEventListener('change', function () {
        document.getElementById('wrapper-account').style.display = this.value === 'confirmado' ? '' : 'none';
        document.getElementById('bank_account_id').required = this.value === 'confirmado';
    });
</script>