<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Nova Transferência | " . APP_NAME,
    "active" => "lancamentos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Nova Transferência entre Contas</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/transferencias/nova') ?>" method="post" id="form-transferencia">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="from_account_id" class="form-label">Conta de Origem</label>
                        <select class="form-select" id="from_account_id" name="from_account_id" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account->getId() ?>">
                                    <?= htmlspecialchars($account->getName()) ?>
                                    (R$ <?= number_format($account->getCurrentBalance() ?? 0, 2, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="to_account_id" class="form-label">Conta de Destino</label>
                        <select class="form-select" id="to_account_id" name="to_account_id" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account->getId() ?>">
                                    <?= htmlspecialchars($account->getName()) ?>
                                    (R$ <?= number_format($account->getCurrentBalance() ?? 0, 2, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <input type="text" class="form-control currency-mask" id="amount_display"
                               data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                        <input type="hidden" id="amount" name="amount" value="<?= old('amount', '0.00') ?>"/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="transfer_date" class="form-label">Data</label>
                        <input type="date" class="form-control" id="transfer_date" name="transfer_date"
                               value="<?= old('transfer_date', date('Y-m-d')) ?>" required/>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Descrição <small class="text-body-secondary">(opcional)</small></label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= old('description') ?>" placeholder="Ex: Reserva de emergência"/>
                </div>

                <button class="btn btn-primary" type="submit">Transferir</button>
                <a href="<?= url('/transferencias') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script src="<?= assets('/js/transfer-form.js') ?>"></script>