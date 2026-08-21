<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Quem Usa Meu Cartão | " . APP_NAME,
        "active" => "pessoas-cartao",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Quem Usa Meu Cartão</h4>
        <a href="<?= url('/pessoas-cartao/nova') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Pessoa
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cartões</th>
                    <th>Telefone</th>
                    <th class="text-end">Total Gasto</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($cardUsers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-6">Nenhuma pessoa cadastrada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($cardUsers as $cardUser): ?>
                    <tr>
                        <td><?= htmlspecialchars($cardUser->getName()) ?></td>
                        <td><?= htmlspecialchars($cardUser->getLinkedCardNames() ?: '-') ?></td>
                        <td>
                            <?php if ($cardUser->getPhone()): ?>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= htmlspecialchars($cardUser->getFormattedPhone()) ?></span>
                                    <a href="<?= htmlspecialchars($cardUser->getWhatsappLink()) ?>"
                                       target="_blank" rel="noopener"
                                       class="btn btn-icon btn-sm btn-outline-success btn-icon-soft-success"
                                       title="Chamar no WhatsApp">
                                        <i class="icon-base bx bxl-whatsapp"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            R$ <?= number_format($cardUser->totalSpent(), 2, ',', '.') ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('/pessoas-cartao/' . $cardUser->getId() . '/editar') ?>"
                               class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                <i class="icon-base bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modal-excluir-pessoa-cartao"
                                    data-action="<?= url('/pessoas-cartao/' . $cardUser->getId() . '/excluir') ?>"
                                    data-name="<?= htmlspecialchars($cardUser->getName()) ?>">
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
<div class="modal fade" id="modal-excluir-pessoa-cartao" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir pessoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir <strong data-delete-name></strong>? Essa ação não pode ser desfeita.
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