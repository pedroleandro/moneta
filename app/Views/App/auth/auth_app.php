<!DOCTYPE html>
<html
    lang="pt-br"
    class="layout-wide customizer-hide"
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
    <link rel="icon" type="image/x-icon" href="<?= assets_sneat('/assets/img/favicon/favicon.ico') ?>"/>

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

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="<?= assets_sneat('/assets/vendor/css/pages/page-auth.css') ?>"/>

    <!-- Helpers -->
    <script src="<?= assets_sneat('/assets/vendor/js/helpers.js') ?>"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="<?= assets_sneat('/assets/js/config.js') ?>"></script>

    <link rel="stylesheet" href="<?= assets('/css/custom.css') ?>"/>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <script src="<?= assets('/js/form-guard.js') ?>"></script>
</head>

<body>

<?= $this->section('content') ?>



<!-- Core JS -->

<script src="<?= assets_sneat('/assets/vendor/libs/jquery/jquery.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/libs/popper/popper.js') ?>"></script>
<script src="<?= assets_sneat('/assets/vendor/js/bootstrap.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>

<script src="<?= assets_sneat('/assets/vendor/js/menu.js') ?>"></script>

<!-- endbuild -->

<!-- Vendors JS -->

<!-- Main JS -->

<script src="<?= assets_sneat('/assets/js/main.js') ?>"></script>

<!-- Page JS -->

<!-- Place this tag before closing body tag for github widget button. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
