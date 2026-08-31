<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Meu Perfil | " . APP_NAME,
        "active" => "perfil",
]) ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Meu Perfil</h4>

    <?= \App\Core\Message::render() ?>

    <ul class="nav nav-tabs mb-6" role="tablist">
        <li class="nav-item">
            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-dados">
                <i class="icon-base bx bx-user me-1"></i> Dados Pessoais
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seguranca">
                <i class="icon-base bx bx-lock-alt me-1"></i> Segurança
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sessoes">
                <i class="icon-base bx bx-desktop me-1"></i> Sessões Ativas
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===================== ABA 1: DADOS PESSOAIS ===================== -->

        <div class="card mb-6">
            <div class="card-body">
                <h6 class="mb-4">Foto de Perfil</h6>
                <form action="<?= url('/perfil/avatar') ?>" method="post" enctype="multipart/form-data"
                      class="d-flex align-items-center gap-4">
                    <?= csrf_input() ?>
                    <img id="avatar-preview" src="<?= avatarSrc($user->getAvatar(), $user->getName()) ?>"
                         alt="Avatar" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                    <div>
                        <input type="file" class="form-control" id="avatar" name="avatar"
                               accept="image/jpeg,image/png,image/webp" required/>
                        <small class="text-body-secondary">JPG, PNG ou WEBP — máximo 2MB.</small>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" type="submit">Salvar Foto</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-pane fade show active" id="tab-dados">
            <div class="card">
                <div class="card-body">
                    <form action="<?= url('/perfil/dados') ?>" method="post">
                        <?= csrf_input() ?>

                        <h6 class="mb-4">Conta</h6>
                        <div class="row">
                            <div class="col-md-6 mb-6">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($user->getName()) ?>" required/>
                            </div>
                            <div class="col-md-6 mb-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($user->getEmail()) ?>" required/>
                                <?php if (!$user->isEmailVerified()): ?>
                                    <small class="text-warning">E-mail ainda não confirmado.</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-6"/>

                        <h6 class="mb-4">Dados Pessoais</h6>
                        <div class="row">
                            <div class="col-md-4 mb-6">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" class="form-control cpf-mask" id="cpf" name="cpf"
                                       value="<?= htmlspecialchars($profile->getCpf() ?? '') ?>"
                                       placeholder="000.000.000-00"/>
                            </div>
                            <div class="col-md-4 mb-6">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control phone-mask" id="phone" name="phone"
                                       value="<?= htmlspecialchars($profile->getPhone() ?? '') ?>"/>
                            </div>
                            <div class="col-md-4 mb-6">
                                <label for="birth_date" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date"
                                       value="<?= htmlspecialchars($profile->getBirthDate() ?? '') ?>"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-6">
                                <label for="gender" class="form-label">Gênero <small class="text-body-secondary">(opcional)</small></label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Prefiro não informar</option>
                                    <option value="masculino" <?= $profile->getGender() === 'masculino' ? 'selected' : '' ?>>
                                        Masculino
                                    </option>
                                    <option value="feminino" <?= $profile->getGender() === 'feminino' ? 'selected' : '' ?>>
                                        Feminino
                                    </option>
                                    <option value="outro" <?= $profile->getGender() === 'outro' ? 'selected' : '' ?>>
                                        Outro
                                    </option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-6"/>

                        <h6 class="mb-4">Endereço</h6>
                        <div class="row">
                            <div class="col-md-3 mb-6">
                                <label for="zip_code" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code"
                                       value="<?= htmlspecialchars($profile->getZipCode() ?? '') ?>"/>
                            </div>
                            <div class="col-md-6 mb-6">
                                <label for="address" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="address" name="address"
                                       value="<?= htmlspecialchars($profile->getAddress() ?? '') ?>"/>
                            </div>
                            <div class="col-md-3 mb-6">
                                <label for="address_number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="address_number" name="address_number"
                                       value="<?= htmlspecialchars($profile->getAddressNumber() ?? '') ?>"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-6">
                                <label for="neighborhood" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="neighborhood" name="neighborhood"
                                       value="<?= htmlspecialchars($profile->getNeighborhood() ?? '') ?>"/>
                            </div>
                            <div class="col-md-5 mb-6">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= htmlspecialchars($profile->getCity() ?? '') ?>"/>
                            </div>
                            <div class="col-md-2 mb-6">
                                <label for="state" class="form-label">UF</label>
                                <input type="text" maxlength="2" class="form-control" id="state" name="state"
                                       value="<?= htmlspecialchars($profile->getState() ?? '') ?>"/>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===================== ABA 2: SEGURANÇA ===================== -->
        <div class="tab-pane fade" id="tab-seguranca">

            <div class="card mb-6">
                <div class="card-body">
                    <h6 class="mb-4"><?= $user->hasPassword() ? 'Trocar Senha' : 'Criar Senha' ?></h6>

                    <?php if (!$user->hasPassword()): ?>
                        <p class="text-body-secondary mb-4">
                            Sua conta ainda não tem senha (você entra só pelo login social).
                            Crie uma senha para também poder entrar com e-mail e senha.
                        </p>
                    <?php endif; ?>

                    <form action="<?= url('/perfil/senha') ?>" method="post">
                        <?= csrf_input() ?>

                        <?php if ($user->hasPassword()): ?>
                            <div class="mb-6">
                                <label for="current_password" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="current_password"
                                       name="current_password" required/>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-6">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required/>
                            </div>
                            <div class="col-md-6 mb-6">
                                <label for="password-confirm" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="password-confirm"
                                       name="password-confirm" required/>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <?= $user->hasPassword() ? 'Trocar Senha' : 'Criar Senha' ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-4">Contas Vinculadas</h6>

                    <?php if (empty($socialAccounts)): ?>
                        <p class="text-body-secondary">Nenhuma conta social vinculada ainda.</p>
                    <?php endif; ?>

                    <?php foreach ($socialAccounts as $socialAccount): ?>
                        <div class="d-flex align-items-center justify-content-between border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <i class="icon-base bx <?= $socialAccount->getProvider() === 'google' ? 'bxl-google' : 'bxl-facebook-circle' ?> icon-lg me-3"></i>
                                <span class="text-capitalize"><?= htmlspecialchars($socialAccount->getProvider()) ?></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modal-desvincular-social"
                                    data-action="<?= url('/perfil/social/desvincular') ?>"
                                    data-name="<?= htmlspecialchars(ucfirst($socialAccount->getProvider())) ?>"
                                    data-extra-id="<?= $socialAccount->getId() ?>">
                                Desvincular
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ===================== ABA 3: SESSÕES ATIVAS ===================== -->
        <div class="tab-pane fade" id="tab-sessoes">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="mb-0">Onde você está logado</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#modal-encerrar-outras-sessoes">
                            Encerrar todas as outras
                        </button>
                    </div>

                    <?php foreach ($sessions as $session): ?>
                        <div class="d-flex align-items-center justify-content-between border-bottom py-3">
                            <div>
                                <div>
                                    <?= htmlspecialchars($session->ip_address ?? 'IP desconhecido') ?>
                                    <?php if ($session->id === $currentSessionId): ?>
                                        <span class="badge bg-label-success ms-2">Sessão atual</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-body-secondary">
                                    <?= htmlspecialchars($session->user_agent ?? '') ?> —
                                    último acesso: <?= date('d/m/Y H:i', $session->last_activity) ?>
                                </small>
                            </div>
                            <?php if ($session->id !== $currentSessionId): ?>
                                <button type="button" class="btn btn-icon btn-outline-danger btn-icon-soft-danger"
                                        title="Encerrar sessão"
                                        data-bs-toggle="modal" data-bs-target="#modal-encerrar-sessao"
                                        data-action="<?= url('/perfil/sessoes/encerrar') ?>"
                                        data-name="<?= htmlspecialchars($session->ip_address ?? 'esse dispositivo') ?>"
                                        data-extra-id="<?= htmlspecialchars($session->id) ?>">
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: desvincular conta social (compartilhado por todas as linhas) -->
<div class="modal fade" id="modal-desvincular-social" data-delete-modal data-extra-field="id" tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Desvincular conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja desvincular a conta <strong data-delete-name></strong>?
                Você poderá vinculá-la novamente mais tarde.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" action="">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" data-extra-value/>
                    <button type="submit" class="btn btn-danger">Desvincular</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: encerrar uma sessão específica (compartilhado) -->
<div class="modal fade" id="modal-encerrar-sessao" data-delete-modal data-extra-field="id" tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Encerrar sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Encerrar a sessão de <strong data-delete-name></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" action="">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" data-extra-value/>
                    <button type="submit" class="btn btn-danger">Encerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: encerrar todas as outras sessões (ação única, sem dados dinâmicos) -->
<div class="modal fade" id="modal-encerrar-outras-sessoes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Encerrar outras sessões</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Isso vai desconectar todos os outros dispositivos onde você está logado, exceto este.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= url('/perfil/sessoes/encerrar-outras') ?>" method="post">
                    <?= csrf_input() ?>
                    <button type="submit" class="btn btn-danger">Encerrar todas</button>
                </form>
            </div>
        </div>
    </div>
</div>