<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Faturas | " . APP_NAME,
        "active" => "faturas",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Faturas</h4>

    <?= \App\Core\Message::render() ?>

    <?php if (empty($cards)): ?>
        <div class="alert alert-info">Cadastre um cartão de crédito para ver faturas aqui.</div>
    <?php else: ?>
        <div class="d-flex flex-wrap align-items-end gap-4 mb-6">
            <div>
                <label for="cartao" class="form-label">Cartão</label>
                <select class="form-select" id="cartao" style="max-width: 300px;"
                        onchange="window.location.href='<?= url('/faturas') ?>?cartao=' + this.value">
                    <?php foreach ($cards as $card): ?>
                        <option value="<?= $card->getId() ?>" <?= $card->getId() === $selectedCardId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($card->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($showAll): ?>
                <a href="<?= url('/faturas?cartao=' . $selectedCardId) ?>" class="btn btn-outline-secondary">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Ver visão recente
                </a>
            <?php else: ?>
                <a href="<?= url('/faturas?cartao=' . $selectedCardId . '&tudo=1') ?>" class="btn btn-outline-secondary">
                    Ver histórico completo
                </a>
            <?php endif; ?>
        </div>

        <?php
        if (!function_exists('renderInvoiceRow')) {
            function renderInvoiceRow($invoice, bool $isCurrent = false): void {
                $badgeClass = match ($invoice->getStatus()) {
                    'paga' => 'bg-label-success',
                    'fechada' => 'bg-label-warning',
                    default => 'bg-label-secondary',
                };
                ?>
                <tr class="<?= $isCurrent ? 'table-active' : '' ?>">
                    <td data-label="Mês de Referência">
                        <?= date('m/Y', strtotime($invoice->getReferenceMonth())) ?>
                    </td>
                    <td data-label="Fechamento"><?= date('d/m/Y', strtotime($invoice->getClosingDate())) ?></td>
                    <td data-label="Vencimento"><?= date('d/m/Y', strtotime($invoice->getDueDate())) ?></td>
                    <td data-label="Total" class="text-end">
                        R$ <?= number_format($invoice->getRemainingAmount(), 2, ',', '.') ?>
                    </td>
                    <td data-label="Status">
                        <span class="badge <?= $badgeClass ?> text-capitalize"><?= $invoice->getStatus() ?></span>
                    </td>
                    <td data-label="Ações" class="text-end">
                        <a href="<?= url('/faturas/' . $invoice->getId()) ?>" class="btn btn-sm btn-outline-primary">
                            Ver Detalhe
                        </a>
                    </td>
                </tr>
                <?php
            }
        }
        ?>

        <div class="card">
            <div class="table-responsive table-responsive-mobile text-nowrap">
                <table class="table table-datatable">
                    <thead>
                    <tr>
                        <th>Mês de Referência</th>
                        <th>Fechamento</th>
                        <th>Vencimento</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($showAll): ?>
                        <?php if (empty($allInvoices)): ?>
                            <tr class="table-empty-row">
                                <td colspan="6" class="text-center py-6">Nenhuma fatura ainda para esse cartão.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($allInvoices as $invoice): ?>
                            <?php renderInvoiceRow($invoice); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if (!$window['current']): ?>
                            <tr class="table-empty-row">
                                <td colspan="6" class="text-center py-6">Nenhuma fatura ainda para esse cartão.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($window['past'] as $invoice): ?>
                            <?php renderInvoiceRow($invoice); ?>
                        <?php endforeach; ?>

                        <?php if ($window['current']): ?>
                            <?php renderInvoiceRow($window['current'], true); ?>
                        <?php endif; ?>

                        <?php foreach ($window['future'] as $invoice): ?>
                            <?php renderInvoiceRow($invoice); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>