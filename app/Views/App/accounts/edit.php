<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Editar Conta | " . APP_NAME,
        "active" => "contas",
]) ?>

<?php
$swatches = [
        '#696cff', '#8592a3', '#71dd37', '#ffab00', '#ff3e1d',
        '#03c3ec', '#233446', '#7367f0', '#28c76f', '#ea5455',
        '#00cfe8', '#ff9f43', '#e83e8c', '#20c997', '#6f42c1', '#495057',
];

$commonIcons = [
        'bx-wallet', 'bx-money', 'bx-credit-card', 'bx-bank', 'bx-coin-stack',
        'bx-trending-up', 'bx-briefcase', 'bx-dollar-circle', 'bx-wallet-alt',
        'bx-line-chart', 'bx-receipt', 'bx-mobile', 'bx-store', 'bx-shield',
];

$currentColor = old('color', $account->getColor() ?: '#696cff');
$currentIcon = old('icon', $account->getIcon() ?: '');
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Conta Bancária</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/contas/' . $account->getId() . '/editar') ?>" method="post">
                <?= csrf_input() ?>

                <div class="row">
                    <div class="col-md-6 mb-6">
                        <label for="name" class="form-label">Nome da Conta</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= old('name', $account->getName()) ?>" required/>
                    </div>

                    <div class="col-md-6 mb-6">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <?php foreach (\App\Models\BankAccount::typeOptions() as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $account->getType() === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-6 position-relative">
                        <label for="bank_name" class="form-label">Banco <small class="text-body-secondary">(opcional — busque por código ou nome)</small></label>
                        <input type="text" class="form-control bank-search-input" id="bank_name" name="bank_name"
                               data-banks-source="banks-data" value="<?= old('bank_name', $account->getBankName()) ?>"
                               placeholder="Digite o código ou nome do banco" autocomplete="off"/>
                        <div id="bank_name_results" class="list-group bank-search-results position-absolute w-100"
                             style="z-index: 1050; max-height: 260px; overflow-y: auto; display: none; top: 100%;"></div>
                    </div>

                    <script type="application/json" id="banks-data"><?= json_encode($banks, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) ?></script>

                    <div class="col-md-6 mb-6">
                        <label for="initial_balance_display" class="form-label">
                            Saldo Inicial
                            <?php if ($account->isInUse()): ?>
                                <small class="text-body-secondary">(bloqueado — conta já tem lançamentos)</small>
                            <?php endif; ?>
                        </label>
                        <input type="text" class="form-control currency-mask" id="initial_balance_display"
                               data-target="#initial_balance" inputmode="numeric" placeholder="R$ 0,00"
                                <?= $account->isInUse() ? 'disabled' : '' ?>/>
                        <input type="hidden" id="initial_balance" name="initial_balance"
                               value="<?= old('initial_balance', $account->getInitialBalance()) ?>"/>
                    </div>
                </div>

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

                <div class="mb-6">
                    <label class="form-label">Ícone <small class="text-body-secondary">(opcional)</small></label>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center border rounded"
                              style="width: 42px; height: 42px;">
                            <i id="icon-preview" class="icon-base bx <?= $currentIcon ?: 'bx-wallet' ?> icon-md"></i>
                        </span>
                        <input type="text" class="form-control font-monospace" id="icon" name="icon"
                               data-default-icon="bx-wallet"
                               value="<?= $currentIcon ?>" style="max-width: 220px;" placeholder="bx-wallet"/>
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

                <div class="mb-6 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            <?= $account->isActive() ? 'checked' : '' ?>/>
                    <label class="form-check-label" for="is_active">Conta ativa</label>
                </div>

                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="<?= url('/contas') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>