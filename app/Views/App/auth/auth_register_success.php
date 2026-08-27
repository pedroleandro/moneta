<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Cadastro Realizado | " . APP_NAME,
]) ?>
<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body text-center">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-6">
                        <a href="<?= url('/entrar') ?>" class="app-brand-link">
                            <img src="<?= assets('/images/moneta_logo_vertical.png') ?>" alt="Moneta"
                                 style="max-width: 160px; height: auto;">
                        </a>
                    </div>
                    <!-- /Logo -->

                    <div class="avatar avatar-xl mx-auto mb-4">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            <i class="icon-base bx bx-envelope icon-lg" style="font-size: 2rem;"></i>
                        </span>
                    </div>

                    <h4 class="mb-1">Confirme seu e-mail</h4>
                    <p class="mb-6">
                        Sua conta foi criada com sucesso! Enviamos um link de confirmação para o seu
                        e-mail — abra sua caixa de entrada e clique no link para ativar sua conta antes
                        de fazer login.
                    </p>

                    <p class="text-body-secondary small mb-6">
                        Não encontrou o e-mail? Confira também a caixa de spam ou lixo eletrônico.
                    </p>

                    <a class="btn btn-outline-primary d-grid w-100" href="<?= url('/entrar') ?>">
                        <span>Já confirmei — Fazer Login</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->