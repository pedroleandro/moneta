<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Lançamentos | " . APP_NAME,
        "active" => $active ?? "lancamentos-todos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">
            Lançamentos
            <?php if ($filterType === 'receita'): ?>
                <span class="badge bg-label-success">Receitas</span>
            <?php elseif ($filterType === 'despesa'): ?>
                <span class="badge bg-label-danger">Despesas</span>
            <?php endif; ?>
        </h4>
        <a href="<?= url('/lancamentos/novo') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Novo Lançamento
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Conta/Cartão</th>
                    <th class="text-end">Valor</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($transactions)): ?>
                    <tr class="table-empty-row">
                        <td colspan="7" class="text-center py-6">Nenhum lançamento encontrado.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td data-label="Data">
                            <?= date('d/m/Y', strtotime($transaction->getTransactionDate())) ?>
                        </td>
                        <td data-label="Descrição">
                            <?= htmlspecialchars($transaction->getDescription()) ?>
                        </td>
                        <td data-label="Categoria">
                            <div class="d-flex align-items-center justify-content-end justify-content-md-start">
                                <span class="rounded-circle me-2"
                                      style="background-color: <?= htmlspecialchars($transaction->getCategoryColor() ?: '#6c757d') ?>; width: 10px; height: 10px; flex-shrink: 0;"></span>
                                <span><?= htmlspecialchars($transaction->getCategoryName() ?? '-') ?></span>
                            </div>
                        </td>
                        <td data-label="Conta/Cartão">
                            <?= htmlspecialchars($transaction->getBankAccountName() ?? $transaction->getCreditCardName() ?? '-') ?>
                        </td>
                        <td data-label="Valor" class="text-end <?= $transaction->getType() === 'receita' ? 'text-success' : 'text-danger' ?>">
                            <?= $transaction->getType() === 'receita' ? '+' : '-' ?>
                            R$ <?= number_format($transaction->getAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($transaction->isConfirmed()): ?>
                                <span class="badge bg-label-success">Confirmado</span>
                            <?php else: ?>
                                <span class="badge bg-label-warning">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <?php if ($transaction->getType() !== 'transferencia'): ?>
                                <a href="<?= url('/lancamentos/' . $transaction->getId() . '/editar') ?>"
                                   class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                    <i class="icon-base bx bx-edit"></i>
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                    title="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modal-excluir-lancamento"
                                    data-action="<?= url('/lancamentos/' . $transaction->getId() . '/excluir') ?>"
                                    data-name="&quot;<?= htmlspecialchars($transaction->getDescription()) ?>&quot;">
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

<div class="modal fade" id="modal-excluir-lancamento" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o lançamento <strong data-delete-name></strong>?
                Se ele estiver confirmado, o saldo/fatura serão ajustados automaticamente.
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