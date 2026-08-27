<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Redefinir Senha | " . APP_NAME,
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
                    <h4 class="mb-1">Redefinir senha</h4>
                    <p class="mb-6">Escolha uma nova senha para sua conta.</p>

                    <?= \App\Core\Message::render() ?>

                    <form id="formAuthentication" class="mb-6 needs-validation" action="<?= url('/resetar-senha') ?>"
                          method="post">

                        <?= csrf_input() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>"/>

                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Nova senha</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password"
                                        class="form-control no-paste"
                                        name="password"
                                        required
                                        minlength="8"
                                        data-error-required="Digite sua nova senha."
                                        data-error-minlength="A senha precisa ter pelo menos 8 caracteres."
                                        data-feedback-id="password-feedback"
                                        placeholder="············"
                                        autofocus/>
                                <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="invalid-feedback d-block" id="password-feedback"></div>
                        </div>

                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password-confirm">Confirmar nova senha</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password-confirm"
                                        class="form-control no-paste"
                                        name="password-confirm"
                                        required
                                        data-error-required="Digite a nova senha novamente para confirmar."
                                        data-feedback-id="password-confirm-feedback"
                                        placeholder="············"/>
                                <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="invalid-feedback d-block" id="password-confirm-feedback"></div>
                        </div>

                        <button class="btn btn-primary d-grid w-100">Redefinir senha</button>
                    </form>

                    <div class="text-center">
                        <a href="<?= url('/entrar') ?>" class="d-flex justify-content-center">
                            <i class="icon-base bx bx-chevron-left me-1"></i>
                            Voltar para Login
                        </a>
                    </div>
                </div>
            </div>
            <!-- Register Card -->
        </div>
    </div>
</div>
<!-- / Content -->