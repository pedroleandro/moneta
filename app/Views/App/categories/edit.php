<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Editar Categoria | " . APP_NAME,
        "active" => "categorias",
]) ?>

<?php
$swatches = [
        '#696cff', '#8592a3', '#71dd37', '#ffab00', '#ff3e1d',
        '#03c3ec', '#233446', '#7367f0', '#28c76f', '#ea5455',
        '#00cfe8', '#ff9f43', '#e83e8c', '#20c997', '#6f42c1', '#495057',
];

$commonIcons = [
        'bx-cart', 'bx-restaurant', 'bx-home', 'bx-car', 'bx-bus',
        'bx-gas-pump', 'bx-plane', 'bx-wallet', 'bx-credit-card', 'bx-money',
        'bx-coin-stack', 'bx-briefcase', 'bx-gift', 'bx-heart', 'bx-dumbbell',
        'bx-movie', 'bx-game', 'bx-shopping-bag', 'bx-phone', 'bx-wifi',
        'bx-bulb', 'bx-water', 'bx-food-menu', 'bx-book', 'bx-pill',
        'bx-cut', 'bx-paw', 'bx-child', 'bx-graduation', 'bx-dollar-circle',
        'bx-receipt', 'bx-trending-up', 'bx-trending-down', 'bx-building-house', 'bx-store',
];

$currentColor = old('color', $category->getColor() ?: '#696cff');
$currentIcon = old('icon', $category->getIcon() ?: '');
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Categoria</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/categorias/' . $category->getId() . '/editar') ?>" method="post">
                <?= csrf_input() ?>

                <div class="mb-6">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= old('name', $category->getName()) ?>" required/>
                </div>

                <div class="mb-6">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="receita" <?= $category->getType() === 'receita' ? 'selected' : '' ?>>Receita</option>
                        <option value="despesa" <?= $category->getType() === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                    </select>
                </div>

                <!-- Seletor de Cor -->
                <div class="mb-6">
                    <label class="form-label">Cor</label>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span id="color-preview" class="rounded-circle border"
                              style="width: 36px; height: 36px; display: inline-block; background-color: <?= $currentColor ?>;"></span>
                        <input type="text" class="form-control font-monospace" id="color" name="color"
                               value="<?= $currentColor ?>" maxlength="7" style="max-width: 150px;"
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

                <!-- Seletor de Ícone -->
                <div class="mb-6">
                    <label class="form-label">Ícone <small class="text-body-secondary">(opcional)</small></label>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center border rounded"
                              style="width: 42px; height: 42px;">
                            <i id="icon-preview" class="icon-base bx <?= $currentIcon ?: 'bx-category' ?> icon-md"></i>
                        </span>
                        <input type="text" class="form-control font-monospace" id="icon" name="icon"
                               value="<?= $currentIcon ?>" style="max-width: 220px;" placeholder="bx-cart"/>
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

                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="<?= url('/categorias') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const colorInput = document.getElementById('color');
        const colorPreview = document.getElementById('color-preview');
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('icon-preview');

        document.querySelectorAll('.color-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const color = btn.dataset.color;
                colorInput.value = color;
                colorPreview.style.backgroundColor = color;
            });
        });

        colorInput.addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(colorInput.value)) {
                colorPreview.style.backgroundColor = colorInput.value;
            }
        });

        document.querySelectorAll('.icon-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const icon = btn.dataset.icon;
                iconInput.value = icon;
                iconPreview.className = 'icon-base bx ' + icon + ' icon-md';
            });
        });

        iconInput.addEventListener('input', function () {
            const value = iconInput.value.trim() || 'bx-category';
            iconPreview.className = 'icon-base bx ' + value + ' icon-md';
        });
    });
</script>