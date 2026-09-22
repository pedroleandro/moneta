<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Nova Recorrência | " . APP_NAME,
    "active" => "recorrencias",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Nova Recorrência</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/recorrencias/nova') ?>" method="post">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="description" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?= old('description') ?>" placeholder="Ex: Salário, Aluguel, Netflix" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <input type="text" class="form-control currency-mask" id="amount_display"
                               data-target="#amount" inputmode="numeric" placeholder="R$ 0,00"/>
                        <input type="hidden" id="amount" name="amount" value="<?= old('amount', '0.00') ?>"/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-6">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Selecione...</option>
                            <option value="receita" <?= old('type') === 'receita' ? 'selected' : '' ?>>Receita</option>
                            <option value="despesa" <?= old('type') === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>"
                                    <?= (string)old('category_id') === (string)$category->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="day_of_month" class="form-label">Dia do Mês</label>
                        <input type="number" min="1" max="31" class="form-control" id="day_of_month"
                               name="day_of_month" value="<?= old('day_of_month') ?>" required/>
                        <small class="text-body-secondary">Se o mês não tiver esse dia, cai no último dia dele.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="start_date" class="form-label">Data de Início</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?= old('start_date', date('Y-m-d')) ?>" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="end_date" class="form-label">Data de Término <small class="text-body-secondary">(opcional)</small></label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?= old('end_date') ?>"/>
                        <small class="text-body-secondary">Deixe em branco para se repetir indefinidamente.</small>
                    </div>
                </div>

                <hr class="my-6"/>

                <div class="mb-6">
                    <label class="form-label d-block">Forma de Pagamento</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pm_account"
                               value="account" <?= old('payment_method', 'account') === 'account' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pm_account">Conta Bancária</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pm_card"
                               value="card" <?= old('payment_method') === 'card' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pm_card">Cartão de Crédito</label>
                    </div>
                </div>

                <div class="row" id="wrapper-account" style="<?= old('payment_method') === 'card' ? 'display:none;' : '' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="bank_account_id" class="form-label">Conta Bancária</label>
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

                <div class="row" id="wrapper-card" style="<?= old('payment_method') === 'card' ? '' : 'display:none;' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="credit_card_id" class="form-label">Cartão</label>
                        <select class="form-select" id="credit_card_id" name="credit_card_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($cards as $card): ?>
                                <option value="<?= $card->getId() ?>"
                                    <?= (string)old('credit_card_id') === (string)$card->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($card->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/recorrencias') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pmAccount = document.getElementById('pm_account');
        const pmCard = document.getElementById('pm_card');
        const wrapperAccount = document.getElementById('wrapper-account');
        const wrapperCard = document.getElementById('wrapper-card');

        function toggle() {
            const isCard = pmCard.checked;
            wrapperAccount.style.display = isCard ? 'none' : '';
            wrapperCard.style.display = isCard ? '' : 'none';
        }

        pmAccount.addEventListener('change', toggle);
        pmCard.addEventListener('change', toggle);
    });
</script>
