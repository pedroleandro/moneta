<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Esqueceu a senha | " . APP_NAME,
]) ?>

<!-- Content -->

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Forgot Password -->
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
                    <h4 class="mb-1">Esqueceu a senha?</h4>
                    <p class="mb-6">Insira seu e-mail e enviaremos instruções para redefinir sua senha.</p>

                    <?= \App\Core\Message::render() ?>

                    <form id="formAuthentication" class="mb-6" action="<?= url('/redefinir-senha') ?>" method="post">

                        <?= csrf_input() ?>

                        <div class="mb-6">
                            <label for="email" class="form-label">Email</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="Informe seu e-mail cadastrado"
                                    autofocus/>
                        </div>

                        <div class="cf-turnstile mb-8"
                             data-sitekey="<?= TURNSTILE_SITE_KEY ?>"
                             data-theme="light"
                             data-size="flexible"
                             data-language="pt-BR"
                             data-callback="onTurnstileVerified"
                             data-expired-callback="onTurnstileExpired">
                        </div>

                        <button class="btn btn-primary d-grid w-100">Enviar</button>
                    </form>
                    <div class="text-center">
                        <a href="<?= url('/entrar') ?>" class="d-flex justify-content-center">
                            <i class="icon-base bx bx-chevron-left me-1"></i>
                            Voltar para Login
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Forgot Password -->
        </div>
    </div>
</div>

<!-- / Content -->
