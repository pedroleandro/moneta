<!doctype html>

<html
        lang="pt-br"
        class="layout-menu-fixed layout-compact"
        data-assets-path="<?= assets_sneat('/assets/') ?>"
        data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8"/>
    <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

    <title><?= $title ?? APP_NAME ?></title>

    <meta name="description" content=""/>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= assets('/images/moneta_logo.png') ?>"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
            href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
            rel="stylesheet"/>

    <link rel="stylesheet" href="<?= assets_sneat('/assets/vendor/fonts/iconify-icons.css') ?>"/>

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="<?= assets_sneat('/assets/vendor/css/core.css') ?>"/>
    <link rel="stylesheet" href="<?= assets_sneat('/assets/css/demo.css') ?>"/>

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="<?= assets_sneat('/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>"/>

    <!-- endbuild -->

    <link rel="stylesheet" href="<?= assets_sneat('/assets/vendor/libs/apex-charts/apex-charts.css') ?>"/>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="<?= assets_sneat('/assets/vendor/js/helpers.js') ?>"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="<?= assets_sneat('/assets/js/config.js') ?>"></script>

    <link rel="stylesheet" href="<?= assets('/css/custom.css') ?>"/>

    <script src="<?= assets('/js/form-guard.js') ?>"></script>
    <script src="<?= assets('/js/color-icon-picker.js') ?>"></script>
    <script src="<?= assets('/js/currency-mask.js') ?>"></script>
    <script src="<?= assets('/js/delete-modal.js') ?>"></script>
    <script src="<?= assets('/js/bank-search.js') ?>"></script>
    <script src="<?= assets('/js/phone-mask.js') ?>"></script>
    <script src="<?= assets('/js/cpf-mask.js') ?>"></script>
    <script src="<?= assets('/js/cep-lookup.js') ?>"></script>
    <script src="<?= assets('/js/transaction-form.js') ?>"></script>
    <script src="<?= assets('/js/transaction-split.js') ?>"></script>
</head>

<body>
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="<?= url('/dashboard') ?>" class="app-brand-link">
                    <img src="<?= assets('/images/moneta_logo_horizontal.png') ?>" alt="Moneta"
                         style="max-height: 64px; width: auto;">
                </a>
                <a href="" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
                </a>
            </div>

            <div class="menu-divider mt-0"></div>

            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">

                <!-- Dashboard -->
                <li class="menu-item <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= url('/dashboard') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-home-circle"></i>
                        <div class="text-truncate">Dashboard</div>
                    </a>
                </li>

                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Financeiro</span>
                </li>

                <!-- Contas Bancárias -->
                <li class="menu-item <?= ($active ?? '') === 'contas' ? 'active' : '' ?>">
                    <a href="<?= url('/contas') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-wallet"></i>
                        <div class="text-truncate">Contas</div>
                    </a>
                </li>

                <!-- Cartões de Crédito -->
                <li class="menu-item <?= ($active ?? '') === 'cartoes' ? 'active' : '' ?>">
                    <a href="<?= url('/cartoes') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-credit-card"></i>
                        <div class="text-truncate">Cartões</div>
                    </a>
                </li>

                <!-- Lançamentos (com submenu) -->
                <!-- Lançamentos (com submenu) -->
                <li class="menu-item <?= str_starts_with($active ?? '', 'lancamentos') ? 'active open' : '' ?>">
                    <a href="" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-transfer-alt"></i>
                        <div class="text-truncate">Lançamentos</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-todos' ? 'active' : '' ?>">
                            <a href="<?= url('/lancamentos') ?>" class="menu-link">
                                <div class="text-truncate">Todos</div>
                            </a>
                        </li>
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-receitas' ? 'active' : '' ?>">
                            <a href="<?= url('/lancamentos?tipo=receita') ?>" class="menu-link">
                                <div class="text-truncate">Receitas</div>
                            </a>
                        </li>
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-despesas' ? 'active' : '' ?>">
                            <a href="<?= url('/lancamentos?tipo=despesa') ?>" class="menu-link">
                                <div class="text-truncate">Despesas</div>
                            </a>
                        </li>
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-transferencias' ? 'active' : '' ?>">
                            <a href="<?= url('/transferencias') ?>" class="menu-link">
                                <div class="text-truncate">Transferências</div>
                            </a>
                        </li>
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-parcelamentos' ? 'active' : '' ?>">
                            <a href="<?= url('/parcelamentos') ?>" class="menu-link">
                                <div class="text-truncate">Compras Parceladas</div>
                            </a>
                        </li>
                        <li class="menu-item <?= ($active ?? '') === 'lancamentos-recorrencias' ? 'active' : '' ?>">
                            <a href="<?= url('/recorrencias') ?>" class="menu-link">
                                <div class="text-truncate">Recorrências</div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Categorias -->
                <li class="menu-item <?= ($active ?? '') === 'categorias' ? 'active' : '' ?>">
                    <a href="<?= url('/categorias') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-category"></i>
                        <div class="text-truncate">Categorias</div>
                    </a>
                </li>

                <!-- Faturas -->
                <li class="menu-item <?= ($active ?? '') === 'faturas' ? 'active' : '' ?>">
                    <a href="<?= url('/faturas') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-receipt"></i>
                        <div class="text-truncate">Faturas</div>
                    </a>
                </li>

                <!-- Pessoas no Cartão -->
                <li class="menu-item <?= ($active ?? '') === 'pessoas-cartao' ? 'active' : '' ?>">
                    <a href="<?= url('/pessoas-cartao') ?>" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-group"></i>
                        <div class="text-truncate">Quem Paga Comigo</div>
                    </a>
                </li>

                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Análises</span>
                </li>

                <!-- Relatórios (com submenu) -->
                <li class="menu-item <?= ($active ?? '') === 'relatorios' ? 'active open' : '' ?>">
                    <a href="" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                        <div class="text-truncate">Relatórios</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="<?= url('/relatorios/categorias') ?>" class="menu-link">
                                <div class="text-truncate">Gastos por Categoria</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= url('/relatorios/evolucao') ?>" class="menu-link">
                                <div class="text-truncate">Evolução Mensal</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="<?= url('/relatorios/exportar') ?>" class="menu-link">
                                <div class="text-truncate">Exportar</div>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            <nav
                    class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
                    id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                        <i class="icon-base bx bx-menu icon-md"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                    <!-- Search -->
                    <div class="navbar-nav align-items-center me-auto flash-message-wrapper">
                        <?= \App\Core\Message::render() ?>
                    </div>
                    <!-- /Search -->

                    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                        <!-- Place this tag where you want the button to render. -->

                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a
                                    class="nav-link dropdown-toggle hide-arrow p-0"
                                    href=""
                                    data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    <img src="<?= assets_sneat('/assets/img/avatars/1.png') ?>" alt
                                         class="w-px-40 h-auto rounded-circle"/>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar avatar-online">
                                                    <img src="<?= assets_sneat('/assets/img/avatars/1.png') ?>" alt
                                                         class="w-px-40 h-auto rounded-circle"/>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?= htmlspecialchars(\App\Core\Auth::user()->name ?? "") ?></h6>
                                                <small class="text-body-secondary"><?= htmlspecialchars(\App\Core\Auth::user()->email ?? "") ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider my-1"></div>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= url('/perfil') ?>">
                                        <i class="icon-base bx bx-user icon-md me-3"></i><span>Perfil</span>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider my-1"></div>
                                </li>
                                <li>
                                    <form action="<?= url('/sair') ?>" method="post">
                                        <?= csrf_input() ?>
                                        <button type="submit" class="dropdown-item">
                                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Sair</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <!--/ User -->
                    </ul>
                </div>
            </nav>

            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <?= $this->section("content") ?>
                <!-- / Content -->

                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div
                                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &#169;
                                <?= date("Y") ?>
                                , Desenvolvido com ❤️ por
                                <a href="https://github.com/pedroleandro" target="_blank"
                                   class="footer-link"><?= APP_DEVELOPER ?></a>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->

<!-- Core JS -->

<script src="<?= assets_sneat('/assets/vendor/libs/jquery/jquery.js') ?>"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= assets('/js/datatable-init.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/libs/popper/popper.js') ?>"></script>
<script src="<?= assets_sneat('/assets/vendor/js/bootstrap.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/js/menu.js') ?>"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="<?= assets_sneat('/assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>

<!-- Main JS -->

<script src="<?= assets_sneat('/assets/js/main.js') ?>"></script>

<!-- Page JS -->
<script src="<?= assets_sneat('/assets/js/dashboards-analytics.js') ?>"></script>
<script src="<?= assets_sneat('/assets/js/navbar-flash-autodismiss.js') ?>"></script>

<!-- Place this tag before closing body tag for github widget button. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>