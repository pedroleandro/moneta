<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Editar Recorrência | " . APP_NAME,
    "active" => "recorrencias",
]) ?>

<?php
$isCardSelected = old('payment_method', $recurrence->isCardRecurrence() ? 'card' : 'account') === 'card';
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Recorrência</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/recorrencias/' . $recurrence->getId() . '/editar') ?>" method="post">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="description" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?= old('description', $recurrence->getDescription()) ?>" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <input type="text" class="form-control currency-mask" id="amount_display"
                               data-target="#amount" inputmode="numeric" placeholder="R$ 0,00"/>
                        <input type="hidden" id="amount" name="amount"
                               value="<?= old('amount', $recurrence->getAmount()) ?>"/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-6">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="receita" <?= old('type', $recurrence->getType()) === 'receita' ? 'selected' : '' ?>>Receita</option>
                            <option value="despesa" <?= old('type', $recurrence->getType()) === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>"
                                    <?= (string)old('category_id', (string)$recurrence->getCategoryId()) === (string)$category->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="day_of_month" class="form-label">Dia do Mês</label>
                        <input type="number" min="1" max="31" class="form-control" id="day_of_month"
                               name="day_of_month" value="<?= old('day_of_month', $recurrence->getDayOfMonth()) ?>" required/>
                        <small class="text-body-secondary">Alterar aqui só afeta a PRÓXIMA ocorrência em diante.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label class="form-label">Data de Início</label>
                        <input type="date" class="form-control" value="<?= $recurrence->getStartDate() ?>" disabled/>
                        <small class="text-body-secondary">Não pode ser alterada após a criação.</small>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="end_date" class="form-label">Data de Término <small class="text-body-secondary">(opcional)</small></label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?= old('end_date', $recurrence->getEndDate()) ?>"/>
                    </div>
                </div>

                <hr class="my-6"/>

                <div class="mb-6">
                    <label class="form-label d-block">Forma de Pagamento</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pm_account"
                               value="account" <?= !$isCardSelected ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pm_account">Conta Bancária</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pm_card"
                               value="card" <?= $isCardSelected ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pm_card">Cartão de Crédito</label>
                    </div>
                </div>

                <div class="row" id="wrapper-account" style="<?= $isCardSelected ? 'display:none;' : '' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="bank_account_id" class="form-label">Conta Bancária</label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account->getId() ?>"
                                    <?= (string)old('bank_account_id', (string)$recurrence->getBankAccountId()) === (string)$account->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row" id="wrapper-card" style="<?= $isCardSelected ? '' : 'display:none;' ?>">
                    <div class="col-md-6 mb-6">
                        <label for="credit_card_id" class="form-label">Cartão</label>
                        <select class="form-select" id="credit_card_id" name="credit_card_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($cards as $card): ?>
                                <option value="<?= $card->getId() ?>"
                                    <?= (string)old('credit_card_id', (string)$recurrence->getCreditCardId()) === (string)$card->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($card->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        <?= $recurrence->isActive() ? 'checked' : '' ?>/>
                    <label class="form-check-label" for="is_active">Recorrência ativa</label>
                </div>

                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
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