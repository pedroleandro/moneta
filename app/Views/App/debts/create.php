<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Nova Dívida | " . APP_NAME,
    "active" => "fiado",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Nova Dívida</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/fiado/novo') ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="card_user_id" class="form-label">Pessoa (a quem devo)</label>
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
                           value="<?= old('description') ?>" placeholder="Ex: Fiado no mercadinho" required
                           data-error-required="Informe a descrição."/>
                </div>

                <div class="mb-6">
                    <label for="due_date" class="form-label">Vencimento <small class="text-body-secondary">(opcional)</small></label>
                    <input type="date" class="form-control" id="due_date" name="due_date" value="<?= old('due_date') ?>"/>
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Observações <small class="text-body-secondary">(opcional)</small></label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"><?= old('notes') ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/fiado') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
