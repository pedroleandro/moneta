<?= $this->layout("layouts/app_layout", [
    "title" => $title ?? "Fiado | " . APP_NAME,
    "active" => "fiado",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Fiado — o que eu devo</h4>
        <a href="<?= url('/fiado/novo') ?>" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Nova Dívida
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
                    <th class="text-end">Pago</th>
                    <th class="text-end">Saldo</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($debts)): ?>
                    <tr class="table-empty-row">
                        <td colspan="8" class="text-center py-6">Nenhuma dívida registrada.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($debts as $debt): ?>
                    <tr>
                        <td data-label="Pessoa"><?= htmlspecialchars($debt->getCardUserName()) ?></td>
                        <td data-label="Descrição"><?= htmlspecialchars($debt->getDescription()) ?></td>
                        <td data-label="Valor" class="text-end">
                            R$ <?= number_format($debt->getAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Pago" class="text-end text-success">
                            R$ <?= number_format($debt->getPaidAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Saldo" class="text-end <?= $debt->getRemainingAmount() > 0 ? 'text-danger' : '' ?>">
                            R$ <?= number_format($debt->getRemainingAmount(), 2, ',', '.') ?>
                        </td>
                        <td data-label="Vencimento">
                            <?= $debt->getDueDate() ? date('d/m/Y', strtotime($debt->getDueDate())) : '—' ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($debt->isPaid()): ?>
                                <span class="badge bg-label-success">Pago</span>
                            <?php else: ?>
                                <span class="badge bg-label-warning">Em aberto</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações" class="text-end">
                            <?php if (!$debt->isPaid()): ?>
                                <button type="button" class="btn btn-icon btn-outline-success me-1" title="Registrar pagamento"
                                        data-bs-toggle="modal" data-bs-target="#modal-pagamento-<?= $debt->getId() ?>">
                                    <i class="icon-base bx bx-money"></i>
                                </button>
                                <a href="<?= url('/fiado/' . $debt->getId() . '/editar') ?>"
                                   class="btn btn-icon btn-outline-secondary me-1" title="Editar">
                                    <i class="icon-base bx bx-edit"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($debt->getPaidAmount() <= 0): ?>
                                <button type="button" class="btn btn-icon btn-outline-danger" title="Excluir"
                                        data-bs-toggle="modal" data-bs-target="#modal-excluir-divida"
                                        data-action="<?= url('/fiado/' . $debt->getId() . '/excluir') ?>"
                                        data-name="&quot;<?= htmlspecialchars($debt->getDescription()) ?>&quot;">
                                    <i class="icon-base bx bx-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php if (!$debt->isPaid()): ?>
                    <div class="modal fade" id="modal-pagamento-<?= $debt->getId() ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="post" action="<?= url('/fiado/' . $debt->getId() . '/pagamentos') ?>" class="needs-validation" novalidate>
                                    <?= csrf_input() ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Registrar pagamento</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-body-secondary">
                                            Saldo em aberto: <strong>R$ <?= number_format($debt->getRemainingAmount(), 2, ',', '.') ?></strong>
                                        </p>
                                        <div class="mb-4">
                                            <label class="form-label">Valor</label>
                                            <input type="text" class="form-control currency-mask" data-target="#debt-amount-<?= $debt->getId() ?>"
                                                   inputmode="numeric" placeholder="R$ 0,00" required/>
                                            <input type="hidden" name="amount" id="debt-amount-<?= $debt->getId() ?>" value="0.00"/>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label d-block">Quem paga</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payment_source"
                                                       id="source-account-<?= $debt->getId() ?>" value="account" checked>
                                                <label class="form-check-label" for="source-account-<?= $debt->getId() ?>">Minha conta</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payment_source"
                                                       id="source-card-<?= $debt->getId() ?>" value="card">
                                                <label class="form-check-label" for="source-card-<?= $debt->getId() ?>">Meu cartão</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payment_source"
                                                       id="source-person-<?= $debt->getId() ?>" value="person">
                                                <label class="form-check-label" for="source-person-<?= $debt->getId() ?>">Outra pessoa</label>
                                            </div>
                                        </div>

                                        <div class="mb-4" id="wrapper-account-<?= $debt->getId() ?>">
                                            <label class="form-label">Conta</label>
                                            <select class="form-select" name="bank_account_id">
                                                <option value="" disabled selected>Selecione...</option>
                                                <?php foreach ($accounts as $account): ?>
                                                    <option value="<?= $account->getId() ?>"><?= htmlspecialchars($account->getName()) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-4" id="wrapper-card-<?= $debt->getId() ?>" style="display:none;">
                                            <label class="form-label">Cartão</label>
                                            <select class="form-select" name="credit_card_id">
                                                <option value="" disabled selected>Selecione...</option>
                                                <?php foreach ($cards as $card): ?>
                                                    <option value="<?= $card->getId() ?>"><?= htmlspecialchars($card->getName()) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-body-secondary">Vai entrar como lançamento na fatura aberta.</small>
                                        </div>

                                        <div class="mb-4" id="wrapper-person-<?= $debt->getId() ?>" style="display:none;">
                                            <label class="form-label">Pessoa que pagou</label>
                                            <select class="form-select" name="paying_person_id">
                                                <option value="" disabled selected>Selecione...</option>
                                                <?php foreach ($cardUsers as $cardUser): ?>
                                                    <option value="<?= $cardUser->getId() ?>"><?= htmlspecialchars($cardUser->getName()) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Data</label>
                                            <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>" required/>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Observações</label>
                                            <textarea class="form-control" name="notes" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Registrar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            const accountRadio = document.getElementById('source-account-<?= $debt->getId() ?>');
                            const cardRadio = document.getElementById('source-card-<?= $debt->getId() ?>');
                            const personRadio = document.getElementById('source-person-<?= $debt->getId() ?>');
                            const wrapperAccount = document.getElementById('wrapper-account-<?= $debt->getId() ?>');
                            const wrapperCard = document.getElementById('wrapper-card-<?= $debt->getId() ?>');
                            const wrapperPerson = document.getElementById('wrapper-person-<?= $debt->getId() ?>');

                            function toggle() {
                                wrapperAccount.style.display = accountRadio.checked ? '' : 'none';
                                wrapperCard.style.display = cardRadio.checked ? '' : 'none';
                                wrapperPerson.style.display = personRadio.checked ? '' : 'none';
                            }

                            accountRadio.addEventListener('change', toggle);
                            cardRadio.addEventListener('change', toggle);
                            personRadio.addEventListener('change', toggle);
                        })();
                    </script>
                <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-excluir-divida" data-delete-modal tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir dívida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir a dívida <strong data-delete-name></strong>?
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
