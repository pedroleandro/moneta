<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Recorrências | " . APP_NAME,
    "active" => "recorrencias",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Recorrências</h4>
        <a href="<?= url('/recorrencias/nova') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Recorrência
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table table-datatable">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th class="text-end">Valor</th>
                    <th class="text-center">Dia</th>
                    <th>Conta/Cartão</th>
                    <th class="text-center">Próxima Ocorrência</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($recurrences)): ?>
                    <tr class="table-empty-row">
                        <td colspan="8" class="text-center py-6">Nenhuma recorrência cadastrada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($recurrences as $recurrence): ?>
                    <tr>
                        <td data-label="Descrição"><?= htmlspecialchars($recurrence->getDescription()) ?></td>
                        <td data-label="Tipo">
                            <?php if ($recurrence->getType() === 'receita'): ?>
                                <span class="badge bg-label-success">Receita</span>
                            <?php else: ?>
                                <span class="badge bg-label-danger">Despesa</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Valor" class="text-end">
                            R$ <?= number_format($recurrence->getAmount() ?? 0, 2, ',', '.') ?>
                        </td>
                        <td data-label="Dia" class="text-center">Dia <?= $recurrence->getDayOfMonth() ?></td>
                        <td data-label="Conta/Cartão">
                            <?= $recurrence->isCardRecurrence() ? '<i class="icon-base bx bx-credit-card me-1"></i>Cartão' : '<i class="icon-base bx bx-wallet me-1"></i>Conta' ?>
                        </td>
                        <td data-label="Próxima Ocorrência" class="text-center">
                            <?= (new \DateTime($recurrence->getNextOccurrenceDate()))->format('d/m/Y') ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($recurrence->isActive()): ?>
                                <span class="badge bg-label-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <a href="<?= url('/recorrencias/' . $recurrence->getId() . '/editar') ?>"
                               class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                <i class="icon-base bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modal-excluir-recorrencia"
                                    data-action="<?= url('/recorrencias/' . $recurrence->getId() . '/excluir') ?>"
                                    data-name="<?= htmlspecialchars($recurrence->getDescription()) ?>">
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

<div class="modal fade" id="modal-excluir-recorrencia" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir recorrência</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir a recorrência <strong data-delete-name></strong>? Ocorrências já
                criadas não serão apagadas — só as futuras deixam de ser geradas.
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