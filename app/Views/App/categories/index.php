<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Categorias | " . APP_NAME,
        "active" => "categorias",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Categorias</h4>
        <a href="<?= url('/categorias/nova') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Categoria
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
                    <th>Origem</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-6">Nenhuma categoria cadastrada ainda.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
        <span class="rounded-circle me-2"
              style="background-color: <?= htmlspecialchars($category->getColor() ?: '#6c757d') ?>; width: 20px; height: 20px; flex-shrink: 0;"></span>
                                <span><?= htmlspecialchars($category->getName()) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if ($category->getType() === 'receita'): ?>
                                <span class="badge bg-label-success">Receita</span>
                            <?php else: ?>
                                <span class="badge bg-label-danger">Despesa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($category->isSystemDefault()): ?>
                                <span class="badge bg-label-secondary">Padrão do sistema</span>
                            <?php else: ?>
                                <span class="badge bg-label-primary">Minha categoria</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if (!$category->isSystemDefault()): ?>
                                <a href="<?= url('/categorias/' . $category->getId() . '/editar') ?>"
                                   class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                    <i class="icon-base bx bx-edit"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                        title="Excluir"
                                        data-bs-toggle="modal" data-bs-target="#modal-excluir-categoria"
                                        data-action="<?= url('/categorias/' . $category->getId() . '/excluir') ?>"
                                        data-name="<?= htmlspecialchars($category->getName()) ?>">
                                    <i class="icon-base bx bx-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão (compartilhado por todas as linhas) -->
<div class="modal fade" id="modal-excluir-categoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir a categoria
                <strong id="modal-excluir-categoria-nome"></strong>? Essa ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="modal-excluir-categoria-form" method="post" action="">
                    <?= csrf_input() ?>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-excluir-categoria');

        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const name = button.getAttribute('data-name');

            modal.querySelector('#modal-excluir-categoria-form').setAttribute('action', action);
            modal.querySelector('#modal-excluir-categoria-nome').textContent = name;
        });
    });
</script>