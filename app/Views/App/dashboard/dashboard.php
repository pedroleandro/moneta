<?= $this->layout("auth/auth_app", [
    "title" => $title ?? "Dashboard | " . APP_NAME,
]) ?>

<div class="container-xxl">
    <h1>Olá, <?= htmlspecialchars($user->name ?? "") ?>!</h1>
    <p>Bem-vindo ao Moneta. Esta é uma tela provisória do dashboard.</p>
    <form action="<?= url('/sair') ?>" method="post">
        <?= csrf_input() ?>
        <button type="submit" class="btn btn-danger">Sair</button>
    </form>
</div>
