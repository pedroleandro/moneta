<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Nova Compra Parcelada | " . APP_NAME,
    "active" => "lancamentos",
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
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($cards as $card): ?>
                                <option value="<?= $card->getId() ?>">
                                    <?= htmlspecialchars($card->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>">
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
                        <input type="hidden" id="total_amount" name="total_amount" value="<?= old('total_amount', '0.00') ?>"/>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="installments_count" class="form-label">Número de Parcelas</label>
                        <input type="number" min="2" max="60" class="form-control" id="installments_count"
                               name="installments_count" value="<?= old('installments_count', '2') ?>" required/>
                    </div>

                    <div class="col-md-4 mb-6">
                        <label for="first_installment_date" class="form-label">Data da 1ª Parcela</label>
                        <input type="date" class="form-control" id="first_installment_date"
                               name="first_installment_date" value="<?= old('first_installment_date', date('Y-m-d')) ?>" required/>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="icon-base bx bx-info-circle me-1"></i>
                    O valor total consome o limite do cartão de uma vez só, na hora da compra —
                    não parcela por parcela. As parcelas serão distribuídas automaticamente
                    nas faturas seguintes, respeitando o dia de fechamento do cartão.
                </div>

                <hr class="my-6"/>

                <label class="form-label d-block mb-3">
                    Dividir com pessoas <small class="text-body-secondary">(opcional — aplicado proporcionalmente em cada parcela)</small>
                </label>

                <div id="splits-container"></div>

                <button type="button" id="add-split-btn" class="btn btn-sm btn-outline-primary mb-6">
                    <i class="icon-base bx bx-plus"></i> Adicionar pessoa
                </button>

                <template id="split-row-template">
                    <div class="row split-row mb-3 align-items-center">
                        <div class="col-md-6">
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
                        <div class="col-md-5">
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   name="split_amount[]" placeholder="Valor total da pessoa"/>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-icon btn-outline-danger remove-split-row">
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