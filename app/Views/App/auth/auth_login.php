<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Entrar | " . APP_NAME,
]) ?>
<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Register -->
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
                    <h4 class="mb-1">Bem-vindo ao Moneta!</h4>
                    <p class="mb-6">Por favor, faça login na sua conta e comece a gerenciar suas contas.</p>
                    <?= \App\Core\Message::render() ?>
                    <form id="formAuthentication" class="mb-6 needs-validation" action="<?= url('/entrar') ?>" method="post">
                        <input type="hidden" name="cf-turnstile-response" class="turnstile-token-field"/>
                        <?= csrf_input() ?>
                        <div class="mb-6">
                            <label for="email" class="form-label">Email</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    value="<?= old('email') ?>"
                                    placeholder="Informe seu email"
                                    required
                                    data-error-required="Informe seu e-mail para entrar."
                                    autofocus/>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Senha</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        placeholder="············"
                                        required
                                        data-error-required="Informe sua senha."
                                        data-feedback-id="password-feedback"
                                        aria-describedby="password"/>
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="invalid-feedback d-block" id="password-feedback"></div>
                        </div>
                        <div class="mb-8">
                            <div class="d-flex justify-content-between">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="remember-me"/>
                                    <label class="form-check-label" for="remember-me"> Lembrar de mim </label>
                                </div>
                                <a href="<?= url('/esqueceu-senha') ?>">
                                    <span>Esqueceu a senha?</span>
                                </a>
                            </div>
                        </div>
                        <div class="mb-6">
                            <button class="btn btn-primary d-grid w-100" type="submit" disabled>Entrar</button>
                        </div>
                    </form>
                    <div class="cf-turnstile mb-8"
                         data-sitekey="<?= TURNSTILE_SITE_KEY ?>"
                         data-theme="light"
                         data-size="flexible"
                         data-language="pt-BR"
                         data-callback="onTurnstileVerified"
                         data-expired-callback="onTurnstileExpired">
                    </div>
                    <div class="divider my-6">
                        <div class="divider-text">ou</div>
                    </div>
                    <div class="d-grid gap-3 mb-6">
                        <form action="<?= url('/entrar/google') ?>" method="post">
                            <?= csrf_input() ?>
                            <input type="hidden" name="cf-turnstile-response" class="turnstile-token-field"/>
                            <button type="submit" class="btn btn-social btn-social-google w-100">
                                <svg width="18" height="18" viewBox="0 0 48 48" class="me-2"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#FFC107"
                                          d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                                    <path fill="#FF3D00"
                                          d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                                    <path fill="#4CAF50"
                                          d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                                    <path fill="#1976D2"
                                          d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                                </svg> Continuar com Google
                            </button>
                        </form>
                        <form action="<?= url('/entrar/facebook') ?>" method="post" class="mt-3">
                            <?= csrf_input() ?>
                            <input type="hidden" name="cf-turnstile-response" class="turnstile-token-field"/>
                            <button type="submit" class="btn btn-social btn-social-facebook w-100">
                                <i class="icon-base bx bxl-facebook-circle me-2"></i> Continuar com Facebook
                            </button>
                        </form>
                    </div>
                    <p class="text-center">
                        <span>Novo em nossa plataforma?</span>
                        <a href="<?= url('/cadastrar') ?>">
                            <span>Criar Conta</span>
                        </a>
                    </p>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
</div>
<!-- / Content -->