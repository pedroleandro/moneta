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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center"
             role="button" data-bs-toggle="collapse" data-bs-target="#filtrosLancamentos"
             aria-expanded="<?= !empty($filters) ? 'true' : 'false' ?>" aria-controls="filtrosLancamentos">
            <h6 class="mb-0"><i class="icon-base bx bx-filter-alt me-1"></i> Filtros</h6>
            <i class="icon-base bx bx-chevron-down"></i>
        </div>
        <div class="collapse <?= !empty($filters) ? 'show' : '' ?>" id="filtrosLancamentos">
            <div class="card-body">
                <form method="get" action="<?= url('/lancamentos') ?>">
                    <?php if ($filterType): ?>
                        <input type="hidden" name="tipo" value="<?= htmlspecialchars($filterType) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label" for="data_inicio">De</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                   value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label" for="data_fim">Até</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim"
                                   value="<?= htmlspecialchars($filters['data_fim'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label" for="categoria_id">Categoria</label>
                            <select class="form-select" id="categoria_id" name="categoria_id">
                                <option value="">Todas</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->getId() ?>"
                                            <?= (int)($filters['categoria_id'] ?? 0) === $category->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="pagamento">Conta/Cartão</label>
                            <select class="form-select" id="pagamento" name="pagamento">
                                <option value="">Todas</option>
                                <?php if (!empty($accounts)): ?>
                                    <optgroup label="Contas">
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="conta:<?= $account->getId() ?>"
                                                    <?= ($filters['pagamento'] ?? '') === 'conta:' . $account->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($account->getName()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <?php if (!empty($cards)): ?>
                                    <optgroup label="Cartões">
                                        <?php foreach ($cards as $card): ?>
                                            <option value="cartao:<?= $card->getId() ?>"
                                                    <?= ($filters['pagamento'] ?? '') === 'cartao:' . $card->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($card->getName()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="confirmado" <?= ($filters['status'] ?? '') === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                <option value="pendente" <?= ($filters['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            </select>
                        </div>
                        <?php if (!empty($cardUsers)): ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label" for="pessoa_id">Pessoa</label>
                                <select class="form-select" id="pessoa_id" name="pessoa_id">
                                    <option value="">Todas</option>
                                    <?php foreach ($cardUsers as $cardUser): ?>
                                        <option value="<?= $cardUser->getId() ?>"
                                                <?= (int)($filters['pessoa_id'] ?? 0) === $cardUser->getId() ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cardUser->getName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-filter-alt me-1"></i> Filtrar
                        </button>
                        <?php if (!empty($filters)): ?>
                            <a href="<?= url('/lancamentos') . ($filterType ? '?tipo=' . $filterType : '') ?>"
                               class="btn btn-outline-secondary">
                                Limpar filtros
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table table-datatable">
                <thead>
                <tr>
                    <th>Data da Compra</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Conta/Cartão</th>
                    <th class="text-end">Valor</th>
                    <th>Status</th>
                    <th>Vencimento</th>
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
                        <td data-label="Data da Compra">
                            <?= date('d/m/Y', strtotime($transaction->getPurchaseDate() ?? $transaction->getTransactionDate())) ?>
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
                        <?php
                        $isPositive = $transaction->getType() === 'receita'
                                || ($transaction->getType() === 'transferencia' && !$transaction->isTransferOutgoing());
                        ?>
                        <td data-label="Valor" class="text-end <?= $isPositive ? 'text-success' : 'text-danger' ?>">
                            <?= $isPositive ? '+' : '-' ?>
                            R$ <?= number_format($transaction->getAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($transaction->isConfirmed()): ?>
                                <span class="badge bg-label-success">Confirmado</span>
                            <?php else: ?>
                                <span class="badge bg-label-warning">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Vencimento">
                            <?php if ($transaction->getCreditCardId() && $transaction->getInvoiceDueDate()): ?>
                                <?= date('d/m/Y', strtotime($transaction->getInvoiceDueDate())) ?>
                            <?php else: ?>
                                <span class="text-body-secondary">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <?php
                            $isLocked = $transaction->getCreditCardId()
                                    && in_array($transaction->getInvoiceStatus(), ['fechada', 'paga'], true);
                            ?>
                            <?php if ($transaction->getType() !== 'transferencia'): ?>
                                <?php if ($isLocked): ?>
                                    <button type="button" class="btn btn-icon btn-outline-secondary me-1" disabled
                                            title="Fatura já fechada — não é possível editar">
                                        <i class="icon-base bx bx-edit"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="<?= url('/lancamentos/' . $transaction->getId() . '/editar') ?>"
                                       class="btn btn-icon btn-outline-secondary btn-icon-soft-primary me-1" title="Editar">
                                        <i class="icon-base bx bx-edit"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($isLocked): ?>
                                <button type="button" class="btn btn-icon btn-outline-secondary" disabled
                                        title="Fatura já fechada — não é possível excluir">
                                    <i class="icon-base bx bx-trash"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                        title="Excluir"
                                        data-bs-toggle="modal" data-bs-target="#modal-excluir-lancamento"
                                        data-action="<?= url('/lancamentos/' . $transaction->getId() . '/excluir') ?>"
                                        data-name="&quot;<?= htmlspecialchars($transaction->getDescription()) ?>&quot;">
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