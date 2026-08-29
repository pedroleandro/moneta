<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Dashboard | " . APP_NAME,
        "active" => "dashboard",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-6 gap-3">
        <h5 class="mb-0">Resumo do mês</h5>
        <div>
            <label for="mes" class="visually-hidden">Mês</label>
            <input type="month" class="form-control" id="mes"
                   min="<?= $minMonth ?>" max="<?= $maxMonth ?>"
                   value="<?= $selectedMonth ?>"
                   onchange="updateDashboardFilters()"/>
        </div>
    </div>

    <div class="row">
        <!-- Saudação + saldo total -->
        <div class="col-12 col-lg-8 mb-6">
            <div class="card h-100">
                <div class="d-flex align-items-center row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                Olá, <?= htmlspecialchars(explode(" ", \App\Core\Auth::user()->name ?? "")[0]) ?>! 👋
                            </h5>
                            <p class="mb-1">Saldo total nas suas contas</p>
                            <h2 class="mb-0 <?= $totalBalance >= 0 ? 'text-success' : 'text-danger' ?>">
                                R$ <?= number_format($totalBalance, 2, ',', '.') ?>
                            </h2>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-6">
                            <img src="<?= assets_sneat('/assets/img/illustrations/man-with-laptop.png') ?>"
                                 height="140" alt="Ilustração"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receita e despesa do mês -->
        <div class="col-12 col-lg-4 mb-6">
            <div class="row h-100">
                <div class="col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar flex-shrink-0 mb-3">
                                <img src="<?= assets_sneat('/assets/img/icons/unicons/chart-success.png') ?>"
                                     alt="Receitas" class="rounded"/>
                            </div>
                            <p class="mb-1">Receitas</p>
                            <h5 class="mb-0 text-success">R$ <?= number_format($monthIncome, 2, ',', '.') ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="avatar flex-shrink-0 mb-3">
                                <img src="<?= assets_sneat('/assets/img/icons/unicons/cc-warning.png') ?>"
                                     alt="Despesas" class="rounded"/>
                            </div>
                            <p class="mb-1">Despesas</p>
                            <h5 class="mb-0 text-danger">R$ <?= number_format($monthExpense, 2, ',', '.') ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evolução receita x despesa -->
        <div class="col-12 col-lg-8 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Evolução — Receita x Despesa</h5>
                </div>
                <div class="card-body">
                    <div id="incomeExpenseChart"></div>
                </div>
            </div>
        </div>

        <!-- Quanto devem pra você -->
        <div class="col-12 col-lg-4 mb-6">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="avatar flex-shrink-0 mb-3">
                        <img src="<?= assets_sneat('/assets/img/icons/unicons/wallet-info.png') ?>" alt="Devendo"
                             class="rounded"/>
                    </div>
                    <p class="mb-1">Quanto devem pra você</p>
                    <h4 class="mb-2">R$ <?= number_format($owedToMe, 2, ',', '.') ?></h4>

                    <?php if (!empty($cardUsers)): ?>
                        <label for="pessoa" class="form-label small text-body-secondary mb-1">Filtrar por pessoa</label>
                        <select class="form-select form-select-sm mb-2" id="pessoa" onchange="updateDashboardFilters()">
                            <option value="">Todas as pessoas</option>
                            <?php foreach ($cardUsers as $cardUser): ?>
                                <option value="<?= $cardUser->getId() ?>" <?= $cardUser->getId() === $selectedPersonId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cardUser->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <small class="text-body-secondary">
                        <?= $selectedPersonId ? 'Nesse mês, com essa pessoa' : 'Somando todas as pessoas nesse mês' ?>
                    </small>

                    <a href="<?= url('/pessoas-cartao') ?>" class="btn btn-sm btn-outline-primary mt-4">
                        Ver todas as pessoas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gastos por categoria -->
        <div class="col-12 col-md-6 col-lg-4 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Gastos por Categoria</h5>
                    <p class="card-subtitle">Mês atual</p>
                </div>
                <div class="card-body">
                    <?php if (empty($topCategories)): ?>
                        <p class="text-body-secondary text-center py-6 mb-0">Nenhum gasto confirmado este mês.</p>
                    <?php endif; ?>

                    <ul class="p-0 m-0">
                        <?php foreach ($topCategories as $category): ?>
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded"
                                          style="background-color: <?= htmlspecialchars($category['category_color'] ?: '#6c757d') ?>; color: #fff;">
                                        <i class="icon-base bx <?= htmlspecialchars($category['category_icon'] ?: 'bx-category') ?>"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0"><?= htmlspecialchars($category['category_name']) ?></h6>
                                    </div>
                                    <div class="user-progress">
                                        <h6 class="mb-0">
                                            R$ <?= number_format((float)$category['total'], 2, ',', '.') ?></h6>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Faturas próximas do vencimento -->
        <div class="col-12 col-md-6 col-lg-4 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Próximas Faturas</h5>
                    <a href="<?= url('/faturas') ?>" class="text-body-secondary">
                        <i class="icon-base bx bx-chevron-right icon-lg"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingInvoices)): ?>
                        <p class="text-body-secondary text-center py-6 mb-0">Nenhuma fatura em aberto.</p>
                    <?php endif; ?>

                    <ul class="p-0 m-0">
                        <?php foreach ($upcomingInvoices as $invoice): ?>
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded" style="background-color: #ffab00; color: #fff;">
                                        <i class="icon-base bx bx-receipt"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <small class="d-block"><?= htmlspecialchars($invoice->getCreditCardName()) ?></small>
                                        <h6 class="fw-normal mb-0">
                                            Vence <?= date('d/m', strtotime($invoice->getDueDate())) ?>
                                        </h6>
                                    </div>
                                    <div class="user-progress">
                                        <h6 class="fw-normal mb-0">
                                            R$ <?= number_format($invoice->getTotalAmount() ?? 0, 2, ',', '.') ?>
                                        </h6>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Últimos lançamentos -->
        <div class="col-12 col-lg-4 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Últimos Lançamentos</h5>
                    <a href="<?= url('/lancamentos') ?>" class="text-body-secondary">
                        <i class="icon-base bx bx-chevron-right icon-lg"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTransactions)): ?>
                        <p class="text-body-secondary text-center py-6 mb-0">Nenhum lançamento ainda.</p>
                    <?php endif; ?>

                    <ul class="p-0 m-0">
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded"
                                          style="background-color: <?= htmlspecialchars($transaction->getCategoryColor() ?: '#6c757d') ?>; color: #fff;">
                                        <i class="icon-base bx <?= htmlspecialchars($transaction->getCategoryIcon() ?: 'bx-transfer-alt') ?>"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <small class="d-block"><?= htmlspecialchars($transaction->getCategoryName() ?? ($transaction->getType() === 'transferencia' ? 'Transferência' : '-')) ?></small>
                                        <h6 class="fw-normal mb-0"><?= htmlspecialchars($transaction->getDescription()) ?></h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="fw-normal mb-0 <?= $transaction->getType() === 'receita' ? 'text-success' : '' ?>">
                                            <?= $transaction->getType() === 'receita' ? '+' : '-' ?>
                                            R$ <?= number_format($transaction->getAmount(), 2, ',', '.') ?>
                                        </h6>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartEl = document.querySelector('#incomeExpenseChart');
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        const chartData = <?= json_encode($chartData) ?>;

        const options = {
            series: [
                {name: 'Receita', data: chartData.income},
                {name: 'Despesa', data: chartData.expense}
            ],
            chart: {
                type: 'area',
                height: 300,
                toolbar: {show: false},
                fontFamily: 'inherit',
                locales: [{
                    name: 'pt-br',
                    options: {
                        months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                        shortMonths: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                        days: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
                        shortDays: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                        toolbar: {
                            exportToSVG: 'Baixar SVG',
                            exportToPNG: 'Baixar PNG',
                            exportToCSV: 'Baixar CSV',
                            menu: 'Menu',
                            selection: 'Selecionar',
                            selectionZoom: 'Zoom da Seleção',
                            zoomIn: 'Aumentar',
                            zoomOut: 'Diminuir',
                            pan: 'Navegação',
                            reset: 'Redefinir Zoom',
                        }
                    }
                }],
                defaultLocale: 'pt-br',
            },
            colors: ['#28c76f', '#dc3545'],
            dataLabels: {enabled: false},
            stroke: {curve: 'smooth', width: 2},
            xaxis: {categories: chartData.labels},
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return 'R$ ' + value.toLocaleString('pt-BR');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            },
            legend: {position: 'top'},
        };

        new ApexCharts(chartEl, options).render();
    });

    function updateDashboardFilters() {
        const mesEl = document.getElementById('mes');
        const pessoaEl = document.getElementById('pessoa');

        const mes = mesEl ? mesEl.value : '';
        const pessoa = pessoaEl ? pessoaEl.value : '';

        let url = '<?= url('/dashboard') ?>?mes=' + encodeURIComponent(mes);

        if (pessoa) {
            url += '&pessoa=' + encodeURIComponent(pessoa);
        }

        window.location.href = url;
    }
</script>