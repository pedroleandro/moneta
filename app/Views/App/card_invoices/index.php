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
        <div class="mb-6">
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

        <div class="card">
            <div class="table-responsive table-responsive-mobile text-nowrap">
                <table class="table">
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
                    <?php if (empty($invoices)): ?>
                        <tr class="table-empty-row">
                            <td colspan="6" class="text-center py-6">Nenhuma fatura ainda para esse cartão.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td data-label="Mês de Referência">
                                <?= date('m/Y', strtotime($invoice->getReferenceMonth())) ?>
                            </td>
                            <td data-label="Fechamento">
                                <?= date('d/m/Y', strtotime($invoice->getClosingDate())) ?>
                            </td>
                            <td data-label="Vencimento">
                                <?= date('d/m/Y', strtotime($invoice->getDueDate())) ?>
                            </td>
                            <td data-label="Total" class="text-end">
                                R$ <?= number_format($invoice->getTotalAmount() ?? 0, 2, ',', '.') ?>
                            </td>
                            <td data-label="Status">
                                <?php
                                $badgeClass = match ($invoice->getStatus()) {
                                    'paga' => 'bg-label-success',
                                    'fechada' => 'bg-label-warning',
                                    default => 'bg-label-secondary',
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> text-capitalize"><?= $invoice->getStatus() ?></span>
                            </td>
                            <td data-label="Ações" class="text-end">
                                <a href="<?= url('/faturas/' . $invoice->getId()) ?>" class="btn btn-sm btn-outline-primary">
                                    Ver Detalhe
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>