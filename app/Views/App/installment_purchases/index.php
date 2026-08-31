<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Compras Parceladas | " . APP_NAME,
        "active" => $active ?? "lancamentos-parcelamentos",
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
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table table-datatable">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Cartão</th>
                    <th class="text-end">Valor Total</th>
                    <th class="text-center">Parcelas</th>
                    <th>Data da Compra</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($purchases)): ?>
                    <tr class="table-empty-row">
                        <td colspan="6" class="text-center py-6">Nenhuma compra parcelada ainda.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td data-label="Descrição"><?= htmlspecialchars($purchase->getDescription()) ?></td>
                        <td data-label="Cartão"><?= htmlspecialchars($purchase->getCreditCardName()) ?></td>
                        <td data-label="Valor Total" class="text-end">
                            R$ <?= number_format($purchase->getTotalAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Parcelas" class="text-center"><?= $purchase->getInstallmentsCount() ?>x</td>
                        <td data-label="Data da Compra">
                            <?= date('d/m/Y', strtotime($purchase->getFirstInstallmentDate())) ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <a href="<?= url('/parcelamentos/' . $purchase->getId() . '/editar') ?>"
                               class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                <i class="icon-base bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Cancelar"
                                    data-bs-toggle="modal" data-bs-target="#modal-cancelar-parcelamento"
                                    data-action="<?= url('/parcelamentos/' . $purchase->getId() . '/excluir') ?>"
                                    data-name="<?= htmlspecialchars($purchase->getDescription()) ?>">
                                <i class="icon-base bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar-parcelamento" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar parcelamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja cancelar <strong data-delete-name></strong>?
                Todas as parcelas ainda não pagas serão excluídas e as faturas afetadas serão recalculadas.
                Isso só é possível se nenhuma parcela já estiver em fatura paga.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                <form method="post" action="">
                    <?= csrf_input() ?>
                    <button type="submit" class="btn btn-danger">Cancelar Parcelamento</button>
                </form>
            </div>
        </div>
    </div>
</div>