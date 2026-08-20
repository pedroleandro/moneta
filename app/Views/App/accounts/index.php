<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Contas Bancárias | " . APP_NAME,
        "active" => "contas",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Contas Bancárias</h4>
        <a href="<?= url('/contas/nova') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Conta
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th class="text-end">Saldo Atual</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-6">Nenhuma conta cadastrada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2"
                                      style="background-color: <?= htmlspecialchars($account->getColor() ?: '#6c757d') ?>; width: 20px; height: 20px; flex-shrink: 0;"></span>
                                <span><?= htmlspecialchars($account->getName()) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($account->getTypeLabel()) ?></td>
                        <td><?= htmlspecialchars($account->getBankName() ?: '-') ?></td>
                        <td class="text-end">
                            R$ <?= number_format($account->getCurrentBalance() ?? 0, 2, ',', '.') ?>
                        </td>
                        <td>
                            <?php if ($account->isActive()): ?>
                                <span class="badge bg-label-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('/contas/' . $account->getId() . '/editar') ?>"
                               class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                <i class="icon-base bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modal-excluir-conta"
                                    data-action="<?= url('/contas/' . $account->getId() . '/excluir') ?>"
                                    data-name="<?= htmlspecialchars($account->getName()) ?>">
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

<!-- Modal de confirmação de exclusão (compartilhado por todas as linhas) -->
<div class="modal fade" id="modal-excluir-conta" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir a conta
                <strong data-delete-name></strong>? Essa ação não pode ser desfeita.
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