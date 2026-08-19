<?= $this->layout("auth/auth_app", [
        "title" => $title ?? "Confirmar cadastro | " . APP_NAME,
]) ?>

<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body text-center">
                    <h4 class="mb-1">Quase lá!</h4>
                    <p class="mb-6">
                        Não encontramos uma conta com o e-mail
                        <strong><?= htmlspecialchars($pending->email) ?></strong>.
                        Confirme para criar sua conta no Moneta.
                    </p>

                    <?= \App\Core\Message::render() ?>

                    <form action="<?= url('/cadastrar/confirmar-social') ?>" method="post" class="text-start">
                        <?= csrf_input() ?>

                        <div class="my-6">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms"/>
                                <label class="form-check-label" for="terms-conditions">
                                    Eu aceito os
                                    <a href="">Políticas de Privacidade e Termos</a>
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-primary d-grid w-100">Criar minha conta</button>
                    </form>

                    <p class="text-center mt-6">
                        <a href="<?= url('/entrar') ?>">Cancelar e voltar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->