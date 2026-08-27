<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Recuperar a Senha | " . APP_NAME,
]) ?>

<!-- Content -->

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Register Card -->
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

                    <h4 class="mb-1">Verifique seu e-mail</h4>
                    <p class="mb-6">
                        Se o e-mail informado estiver cadastrado no Moneta, você vai receber um link para redefinir
                        sua senha. O link expira em 2 horas.
                    </p>

                    <a href="<?= url('/entrar') ?>" class="btn btn-primary d-grid w-100 mb-4">
                        Voltar para o Login
                    </a>

                    <div class="text-center">
                        <a href="<?= url('/esqueceu-senha') ?>" class="d-flex justify-content-center">
                            <i class="icon-base bx bx-chevron-left me-1"></i>
                            Não recebeu? Tentar novamente
                        </a>
                    </div>
                </div>
            </div>
            <!-- Register Card -->
        </div>
    </div>
</div>

    <!-- / Content -->
