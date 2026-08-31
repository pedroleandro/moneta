<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Editar Parcelamento | " . APP_NAME,
    "active" => "lancamentos-parcelamentos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Parcelamento</h4>
    <?= \App\Core\Message::render() ?>
    <div class="card">
        <div class="card-body">
            <form action="<?= url('/parcelamentos/' . $purchase->getId() . '/editar') ?>" method="post"
                  class="needs-validation">
                <?= csrf_input() ?>
                <div class="mb-6">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($purchase->getDescription()) ?>" disabled/>
                    <small class="text-body-secondary">A descrição não pode ser alterada por aqui.</small>
                </div>
                <div class="mb-6">
                    <label for="total_amount_display" class="form-label">Valor Total</label>
                    <input type="text" class="form-control currency-mask" id="total_amount_display"
                           data-target="#total_amount" inputmode="numeric" placeholder="R$ 0,00" required/>
                    <input type="hidden" id="total_amount" name="total_amount"
                           value="<?= number_format($purchase->getTotalAmount(), 2, '.', '') ?>"/>
                    <small class="text-body-secondary">
                        As <?= $purchase->getInstallmentsCount() ?> parcelas serão recalculadas proporcionalmente.
                    </small>
                </div>
                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/parcelamentos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>