<?= $this->layout("auth/auth_app", [
    "title" => $title ?? "Termos de Uso e Política de Privacidade | " . APP_NAME,
]) ?>
<div class="container-xxl container-p-y" style="max-width: 800px;">
    <div class="card">
        <div class="card-body">
            <div class="app-brand justify-content-center mb-6">
                <a href="<?= url('/entrar') ?>" class="app-brand-link">
                    <img src="<?= assets('/images/moneta_logo_vertical.png') ?>" alt="Moneta"
                         style="max-width: 140px; height: auto;">
                </a>
            </div>

            <?= $this->fetch('legal/_termos_conteudo') ?>

            <div class="text-center mt-6">
                <a href="<?= url('/entrar') ?>" class="btn btn-outline-primary">Voltar</a>
            </div>
        </div>
    </div>
</div>