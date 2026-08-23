<?php
$oldSplitPersonIds = old('split_card_user_id', []);
if (!is_array($oldSplitPersonIds)) {
    $oldSplitPersonIds = [];
}
$oldSplitAmounts = old('split_amount', []);
?>
<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Nova Compra Parcelada | " . APP_NAME,
        "active" => "lancamentos-parcelamentos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Nova Compra Parcelada</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/parcelamentos/novo') ?>" method="post" id="form-parcelamento">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="credit_card_id" class="form-label">Cartão</label>
                        <select class="form-select" id="credit_card_id" name="credit_card_id" required>
                            <option value="" disabled <?= old('credit_card_id') ? '' : 'selected' ?>>Selecione...
                            </option>
                            <?php foreach ($cards as $card): ?>
                                <option value="<?= $card->getId() ?>"
                                        <?= (string)old('credit_card_id') === (string)$card->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($card->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled <?= old('category_id') ? '' : 'selected' ?>>Selecione...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>"
                                        <?= (string)old('category_id') === (string)$category->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= old('description') ?>" placeholder="Ex: Notebook novo" required/>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-6">
                        <label for="total_amount_display" class="form-label">Valor Total</label>
                        <input type="text" class="form-control currency-mask" id="total_amount_display"
                               data-target="#total_amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                        <input type="hidden" id="total_amount" name="total_amount"
                               value="<?= old('total_amount', '0.00') ?>"/>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="installments_count" class="form-label">Número de Parcelas</label>
                        <input type="number" class="form-control" id="installments_count"
                               name="installments_count" value="<?= old('installments_count', '2') ?>" required/>
                        <small class="text-body-secondary">Mínimo 2 — pagamento único não é parcelamento.</small>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="purchase_date" class="form-label">Data da Compra</label>
                        <input type="date" class="form-control" id="purchase_date"
                               name="purchase_date" value="<?= old('purchase_date', date('Y-m-d')) ?>" required/>
                        <small class="text-body-secondary">A 1ª parcela cai no vencimento da fatura
                            correspondente.</small>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="icon-base bx bx-info-circle me-1"></i>
                    O valor total consome o limite do cartão de uma vez só, na hora da compra —
                    não parcela por parcela. Se a compra for feita antes do fechamento do cartão,
                    a 1ª parcela entra na fatura atual; se depois, entra na fatura seguinte —
                    e assim por diante, uma parcela por fatura.
                </div>

                <hr class="my-6"/>

                <label class="form-label d-block mb-3">
                    Dividir com pessoas <small class="text-body-secondary">(opcional — aplicado proporcionalmente em
                        cada parcela)</small>
                </label>

                <div id="splits-container">
                    <?php foreach ($oldSplitPersonIds as $index => $personId): ?>
                        <div class="split-row mb-3 p-3 border rounded">
                            <div class="mb-2">
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
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control currency-mask split-amount-display"
                                           inputmode="numeric" placeholder="R$ 0,00"/>
                                    <input type="hidden" name="split_amount[]" class="split-amount-hidden"
                                           value="<?= htmlspecialchars($oldSplitAmounts[$index] ?? '0.00') ?>"/>
                                </div>
                                <button type="button"
                                        class="btn btn-icon btn-outline-danger remove-split-row flex-shrink-0">
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-6">
                    <button type="button" id="add-split-btn" class="btn btn-sm btn-outline-primary">
                        <i class="icon-base bx bx-plus"></i> Adicionar pessoa
                    </button>
                </div>

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

                <button class="btn btn-primary" type="submit">Salvar Parcelamento</button>
                <a href="<?= url('/parcelamentos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>