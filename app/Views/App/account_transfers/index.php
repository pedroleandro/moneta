<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Transferências | " . APP_NAME,
        "active" => $active ?? "lancamentos-transferencias",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Transferências entre Contas</h4>
        <a href="<?= url('/transferencias/nova') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Transferência
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table table-datatable">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>De</th>
                    <th></th>
                    <th>Para</th>
                    <th>Descrição</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($transfers)): ?>
                    <tr class="table-empty-row">
                        <td colspan="7" class="text-center py-6">Nenhuma transferência realizada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($transfers as $transfer): ?>
                    <tr>
                        <td data-label="Data"><?= date('d/m/Y', strtotime($transfer->getTransferDate())) ?></td>
                        <td data-label="De"><?= htmlspecialchars($transfer->getFromAccountName()) ?></td>
                        <td><i class="icon-base bx bx-right-arrow-alt"></i></td>
                        <td data-label="Para"><?= htmlspecialchars($transfer->getToAccountName()) ?></td>
                        <td data-label="Descrição"><?= htmlspecialchars($transfer->getDescription() ?: '-') ?></td>
                        <td data-label="Valor" class="text-end">
                            R$ <?= number_format($transfer->getAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modal-excluir-transferencia"
                                    data-action="<?= url('/transferencias/' . $transfer->getId() . '/excluir') ?>"
                                    data-name="esta transferência">
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

<div class="modal fade" id="modal-excluir-transferencia" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir transferência</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir <strong data-delete-name></strong>?
                Os saldos das duas contas envolvidas serão revertidos automaticamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" action="">
                    <?= csrf_input() ?>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>