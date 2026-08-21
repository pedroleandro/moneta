<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Novo Cartão | " . APP_NAME,
        "active" => "cartoes",
]) ?>

<?php
$swatches = [
        '#696cff', '#8592a3', '#71dd37', '#ffab00', '#ff3e1d',
        '#03c3ec', '#233446', '#7367f0', '#28c76f', '#ea5455',
        '#00cfe8', '#ff9f43', '#e83e8c', '#20c997', '#6f42c1', '#495057',
];

$commonIcons = [
        'bx-credit-card', 'bx-credit-card-alt', 'bx-wallet', 'bx-money', 'bx-dollar-circle',
        'bx-store', 'bx-cart', 'bx-gift', 'bx-plane', 'bx-shield',
        'bx-diamond', 'bx-crown', 'bx-star', 'bx-mobile', 'bx-receipt',
];
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Novo Cartão de Crédito</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/cartoes/novo') ?>" method="post">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="name" class="form-label">Nome do Cartão</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= old('name') ?>" placeholder="Ex: Nubank Ultravioleta" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="card_limit_display" class="form-label">Limite</label>
                        <input type="text" class="form-control currency-mask" id="card_limit_display"
                               data-target="#card_limit" inputmode="numeric" placeholder="R$ 0,00"/>
                        <input type="hidden" id="card_limit" name="card_limit" value="<?= old('card_limit', '0.00') ?>"/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="closing_day" class="form-label">Dia de Fechamento</label>
                        <input type="number" min="1" max="31" class="form-control" id="closing_day"
                               name="closing_day" value="<?= old('closing_day') ?>" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="due_day" class="form-label">Dia de Vencimento</label>
                        <input type="number" min="1" max="31" class="form-control" id="due_day"
                               name="due_day" value="<?= old('due_day') ?>" required/>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label">Cor</label>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span id="color-preview" class="rounded-circle border"
                              style="width: 36px; height: 36px; display: inline-block; background-color: <?= old('color', '#696cff') ?>;"></span>
                        <input type="text" class="form-control font-monospace" id="color" name="color"
                               value="<?= old('color', '#696cff') ?>" maxlength="7" style="max-width: 150px;"
                               placeholder="#696cff"/>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($swatches as $swatch): ?>
                            <button type="button" class="color-swatch-btn border-0 rounded-circle"
                                    data-color="<?= $swatch ?>"
                                    style="width: 28px; height: 28px; background-color: <?= $swatch ?>; cursor: pointer;"
                                    title="<?= $swatch ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label">Ícone <small class="text-body-secondary">(opcional)</small></label>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center border rounded"
                              style="width: 42px; height: 42px;">
                            <i id="icon-preview" class="icon-base bx <?= old('icon') ?: 'bx-credit-card' ?> icon-md"></i>
                        </span>
                        <input type="text" class="form-control font-monospace" id="icon" name="icon"
                               data-default-icon="bx-credit-card"
                               value="<?= old('icon') ?>" style="max-width: 220px;" placeholder="bx-credit-card"/>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($commonIcons as $iconClass): ?>
                            <button type="button" class="icon-swatch-btn btn btn-outline-secondary btn-icon"
                                    data-icon="<?= $iconClass ?>" title="<?= $iconClass ?>">
                                <i class="icon-base bx <?= $iconClass ?>"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Salvar</button>
                <a href="<?= url('/cartoes') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>