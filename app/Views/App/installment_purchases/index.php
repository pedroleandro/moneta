<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Compras Parceladas | " . APP_NAME,
    "active" => "lancamentos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Compras Parceladas</h4>
        <a href="<?= url('/parcelamentos/novo') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Compra Parcelada
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Cartão</th>
                    <th class="text-end">Valor Total</th>
                    <th class="text-center">Parcelas</th>
                    <th>1ª Parcela</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($purchases)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-6">Nenhuma compra parcelada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td><?= htmlspecialchars($purchase->getDescription()) ?></td>
                        <td><?= htmlspecialchars($purchase->getCreditCardName()) ?></td>
                        <td class="text-end">R$ <?= number_format($purchase->getTotalAmount(), 2, ',', '.') ?></td>
                        <td class="text-center"><?= $purchase->getInstallmentsCount() ?>x</td>
                        <td><?= date('d/m/Y', strtotime($purchase->getFirstInstallmentDate())) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>