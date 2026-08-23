<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Novo Lançamento | " . APP_NAME,
        "active" => "lancamentos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Novo Lançamento</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/lancamentos/novo') ?>" method="post" id="form-lancamento">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-4 mb-6">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" disabled <?= old('type') ? '' : 'selected' ?>>Selecione...</option>
                            <option value="receita" <?= old('type') === 'receita' ? 'selected' : '' ?>>Receita</option>
                            <option value="despesa" <?= old('type') === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled <?= old('category_id') ? '' : 'selected' ?>>Selecione o tipo primeiro...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>" data-type="<?= $category->getType() ?>"
                                        <?= (string)old('category_id') === (string)$category->getId() ? 'selected' : '' ?>
                                        <?= old('type') === $category->getType() ? '' : 'style="display:none;"' ?>>
                                    <?= htmlspecialchars($category->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="amount_display" class="form-label">Valor</label>
                        <input type="text" class="form-control currency-mask" id="amount_display"
                               data-target="#amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                        <input type="hidden" id="amount" name="amount" value="<?= old('amount', '0.00') ?>"/>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= old('description') ?>" placeholder="Ex: Supermercado, Salário" required/>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="transaction_date" class="form-label">Data</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date"
                               value="<?= old('transaction_date', date('Y-m-d')) ?>" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pendente" <?= old('status', 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="confirmado" <?= old('status') === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                        </select>
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

                <div id="splits-section" style="<?= old('payment_method') === 'card' ? '' : 'display:none;' ?>">
                    <hr class="my-6"/>
                    <label class="form-label d-block mb-3">
                        Dividir com pessoas <small class="text-body-secondary">(opcional)</small>
                    </label>

                    <?php
                    $oldSplitPersonIds = old('split_card_user_id', []);
                    if (!is_array($oldSplitPersonIds)) {
                        $oldSplitPersonIds = [];
                    }
                    $oldSplitAmounts = old('split_amount', []);
                    ?>

                    <div id="splits-container">
                        <?php foreach ($oldSplitPersonIds as $index => $personId): ?>
                            <div class="row split-row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <select class="form-select" name="split_card_user_id[]">
                                        <option value="">Selecione a pessoa...</option>
                                        <?php foreach ($cardUsers as $cardUser): ?>
                                            <option value="<?= $cardUser->getId() ?>"
                                                    data-cards="<?= implode(',', $cardUser->getLinkedCardIds()) ?>"
                                                    <?= (string)$personId === (string)$cardUser->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cardUser->getName()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control currency-mask split-amount-display"
                                           inputmode="numeric" placeholder="R$ 0,00"/>
                                    <input type="hidden" name="split_amount[]" class="split-amount-hidden"
                                           value="<?= htmlspecialchars($oldSplitAmounts[$index] ?? '0.00') ?>"/>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-icon btn-outline-danger remove-split-row">
                                        <i class="icon-base bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" id="add-split-btn" class="btn btn-sm btn-outline-primary mb-6">
                        <i class="icon-base bx bx-plus"></i> Adicionar pessoa
                    </button>

                    <template id="split-row-template">
                        <div class="row split-row mb-3 align-items-center gx-2">
                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                <select class="form-select" name="split_card_user_id[]">
                                    <option value="">Selecione a pessoa...</option>
                                    <?php foreach ($cardUsers as $cardUser): ?>
                                        <option value="<?= $cardUser->getId() ?>"
                                                data-cards="<?= implode(',', $cardUser->getLinkedCardIds()) ?>">
                                            <?= htmlspecialchars($cardUser->getName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-9 col-md-5">
                                <input type="text" class="form-control currency-mask split-amount-display"
                                       inputmode="numeric" placeholder="R$ 0,00"/>
                                <input type="hidden" name="split_amount[]" class="split-amount-hidden" value="0.00"/>
                            </div>
                            <div class="col-3 col-md-1">
                                <button type="button" class="btn btn-icon btn-outline-danger remove-split-row w-100">
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/lancamentos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>