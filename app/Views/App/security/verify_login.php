<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Verificar acesso | " . APP_NAME,
]) ?>
<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center">
                        <a href="<?= url('/entrar') ?>" class="app-brand-link">
                            <img src="<?= assets('/images/moneta_logo_vertical.png') ?>" alt="Moneta"
                                 style="max-width: 160px; height: auto;">
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1">Verificar acesso</h4>
                    <p class="mb-6">Detectamos um login na sua conta a partir de um dispositivo novo. Foi você?</p>
                    <?= \App\Core\Message::render() ?>

                    <table class="table table-borderless mb-6">
                        <tbody>
                        <tr>
                            <td class="text-body-secondary ps-0">Endereço IP</td>
                            <td class="text-end pe-0"><strong><?= htmlspecialchars($verification->ip_address ?? 'Desconhecido') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-body-secondary ps-0">Localização</td>
                            <td class="text-end pe-0"><strong><?= htmlspecialchars($verification->location ?? 'Não identificada') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-body-secondary ps-0">Data</td>
                            <td class="text-end pe-0"><strong><?= date('d/m/Y \à\s H:i', strtotime($verification->created_at)) ?></strong></td>
                        </tr>
                        </tbody>
                    </table>

                    <form action="<?= url('/seguranca/verificar-login/' . $token . '/confirmar') ?>" method="post" class="mb-3">
                        <?= csrf_input() ?>
                        <button type="submit" class="btn btn-primary d-grid w-100">Sim, fui eu</button>
                    </form>
                    <form action="<?= url('/seguranca/verificar-login/' . $token . '/reportar') ?>" method="post">
                        <?= csrf_input() ?>
                        <button type="submit" class="btn btn-outline-danger d-grid w-100">Não fui eu — proteger minha conta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->