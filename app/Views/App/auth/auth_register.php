<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Cadastrar | " . APP_NAME,
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
                    <h4 class="mb-1">A aventura começa aqui</h4>
                    <p class="mb-6">Torne o gerenciamento das suas finanças fácil e divertido!</p>

                    <?= \App\Core\Message::render() ?>

                    <form id="formAuthentication" class="mb-6 needs-validation" action="<?= url('/cadastrar') ?>" method="post">

                        <?= csrf_input() ?>

                        <div class="mb-6">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= old('name') ?>"
                                   required
                                   minlength="3"
                                   data-error-required="Informe seu nome completo."
                                   data-error-minlength="O nome precisa ter pelo menos 3 caracteres."
                                   placeholder="Informe seu nome completo"/>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= old('email') ?>"
                                   required
                                   data-error-required="Informe seu e-mail."
                                   data-error-type="Digite um e-mail válido, ex: nome@exemplo.com."
                                   placeholder="Informe seu melhor e-mail"/>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-password-toggle">
                            <label class="form-label" for="password">Senha</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password"
                                        class="form-control no-paste"
                                        name="password"
                                        required
                                        minlength="8"
                                        data-error-required="Crie uma senha para sua conta."
                                        data-error-minlength="A senha precisa ter pelo menos 8 caracteres."
                                        data-feedback-id="password-feedback"
                                        placeholder="············"
                                        aria-describedby="password"/>
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="invalid-feedback d-block" id="password-feedback"></div>
                        </div>
                        <br>
                        <div class="form-password-toggle">
                            <label class="form-label" for="password-confirm">Confirmar Senha</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password-confirm"
                                        class="form-control no-paste"
                                        name="password-confirm"
                                        required
                                        data-error-required="Digite a senha novamente para confirmar."
                                        data-feedback-id="password-confirm-feedback"
                                        placeholder="············"
                                        aria-describedby="password-confirm"/>
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="invalid-feedback d-block" id="password-confirm-feedback"></div>
                        </div>
                        <div class="my-7">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms"
                                       required
                                       data-error-required="Você precisa aceitar os termos para criar sua conta."/>
                                <label class="form-check-label" for="terms-conditions">
                                    Eu aceito os
                                    <a href="">Políticas de Privacidade e Termos</a>
                                </label>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="cf-turnstile mb-8"
                             data-sitekey="<?= TURNSTILE_SITE_KEY ?>"
                             data-theme="light"
                             data-size="flexible"
                             data-language="pt-BR"
                             data-callback="onTurnstileVerified"
                             data-expired-callback="onTurnstileExpired">
                        </div>

                        <button class="btn btn-primary d-grid w-100">Cadastrar</button>
                    </form>

                    <p class="text-center">
                        <span>Ja tem uma conta?</span>
                        <a href="<?= url('/entrar') ?>">
                            <span>Fazer Login</span>
                        </a>
                    </p>
                </div>
            </div>
            <!-- Register Card -->
        </div>
    </div>
</div>
<!-- / Content -->