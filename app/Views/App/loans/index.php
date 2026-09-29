<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Empréstimos | " . APP_NAME,
        "active" => "emprestimos",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Empréstimos</h4>
        <a href="<?= url('/emprestimos/novo') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Novo Empréstimo
        </a>
    </div>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="table-responsive table-responsive-mobile text-nowrap">
            <table class="table table-datatable">
                <thead>
                <tr>
                    <th>Pessoa</th>
                    <th>Descrição</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end">Devolvido</th>
                    <th class="text-end">Saldo</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($loans)): ?>
                    <tr class="table-empty-row">
                        <td colspan="8" class="text-center py-6">Nenhum empréstimo registrado.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td data-label="Pessoa"><?= htmlspecialchars($loan->getCardUserName()) ?></td>
                        <td data-label="Descrição"><?= htmlspecialchars($loan->getDescription()) ?></td>
                        <td data-label="Valor" class="text-end">
                            R$ <?= number_format($loan->getAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Devolvido" class="text-end text-success">
                            R$ <?= number_format($loan->getReturnedAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Saldo"
                            class="text-end <?= $loan->getRemainingAmount() > 0 ? 'text-danger' : '' ?>">
                            R$ <?= number_format($loan->getRemainingAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Vencimento">
                            <?= $loan->getDueDate() ? date('d/m/Y', strtotime($loan->getDueDate())) : '—' ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($loan->isPending()): ?>
                                <span class="badge bg-label-warning">Pendente</span>
                            <?php elseif ($loan->isConfirmed()): ?>
                                <span class="badge bg-label-info">Emprestado</span>
                            <?php else: ?>
                                <span class="badge bg-label-success">Quitado</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <?php if ($loan->isConfirmed()): ?>
                                <button type="button" class="btn btn-icon btn-outline-success me-1"
                                        title="Registrar devolução"
                                        data-bs-toggle="modal" data-bs-target="#modal-devolucao-<?= $loan->getId() ?>">
                                    <i class="icon-base bx bx-money"></i>
                                </button>
                            <?php endif; ?>

                            <?php if (!$loan->isSettled()): ?>
                                <a href="<?= url('/emprestimos/' . $loan->getId() . '/editar') ?>"
                                   class="btn btn-icon btn-outline-secondary me-1" title="Editar">
                                    <i class="icon-base bx bx-edit"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($loan->getReturnedAmount() <= 0): ?>
                                <button type="button" class="btn btn-icon btn-outline-danger" title="Excluir"
                                        data-bs-toggle="modal" data-bs-target="#modal-excluir-emprestimo"
                                        data-action="<?= url('/emprestimos/' . $loan->getId() . '/excluir') ?>"
                                        data-name="&quot;<?= htmlspecialchars($loan->getDescription()) ?>&quot;">
                                    <i class="icon-base bx bx-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php if ($loan->isConfirmed()): ?>
                    <div class="modal fade" id="modal-devolucao-<?= $loan->getId() ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="post"
                                      action="<?= url('/emprestimos/' . $loan->getId() . '/devolucoes') ?>"
                                      class="needs-validation" novalidate>
                                    <?= csrf_input() ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Registrar devolução</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-body-secondary">
                                            Saldo em aberto:
                                            <strong>R$ <?= number_format($loan->getRemainingAmount(), 2, ',', '.') ?></strong>
                                        </p>
                                        <div class="mb-4">
                                            <label class="form-label">Valor</label>
                                            <input type="text" class="form-control currency-mask"
                                                   data-target="#amount-<?= $loan->getId() ?>"
                                                   inputmode="numeric" placeholder="R$ 0,00" required
                                                   data-error-required="Informe o valor da devolução."/>
                                            <input type="hidden" name="amount" id="amount-<?= $loan->getId() ?>"
                                                   value="0.00"/>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Conta de destino</label>
                                            <select class="form-select" name="bank_account_id" required
                                                    data-error-required="Selecione a conta.">
                                                <option value="" disabled selected>Selecione...</option>
                                                <?php foreach ($accounts as $account): ?>
                                                    <option value="<?= $account->getId() ?>"><?= htmlspecialchars($account->getName()) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Data</label>
                                            <input type="date" class="form-control" name="payment_date"
                                                   value="<?= date('Y-m-d') ?>" required/>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Observações</label>
                                            <textarea class="form-control" name="notes" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-success" disabled
                                                id="submit-devolucao-<?= $loan->getId() ?>">Registrar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            const form = document.querySelector('#modal-devolucao-<?= $loan->getId() ?> form');
                            const submitButton = document.getElementById('submit-devolucao-<?= $loan->getId() ?>');
                            const amountInput = document.getElementById('amount-<?= $loan->getId() ?>');
                            const requiredFields = form.querySelectorAll('[required]');

                            function updateButtonState() {
                                const amountFilled = parseFloat(amountInput.value.replace(',', '.')) > 0;
                                const allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
                                submitButton.disabled = !(amountFilled && allFilled);
                            }

                            form.addEventListener('input', updateButtonState);
                            form.addEventListener('change', updateButtonState);
                            form.addEventListener('keyup', updateButtonState);
                            form.addEventListener('blur', updateButtonState, true);

                            document.getElementById('modal-devolucao-<?= $loan->getId() ?>')
                                .addEventListener('shown.bs.modal', updateButtonState);

                            setInterval(updateButtonState, 400);
                        })();
                    </script>
                <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-excluir-emprestimo" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir empréstimo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o empréstimo <strong data-delete-name></strong>?
                Se ele estiver confirmado, o saldo da conta será ajustado automaticamente.
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
