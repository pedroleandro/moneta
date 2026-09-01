<?php
/**
 * Variáveis esperadas:
 *   string $name, $verifyUrl
 */
$this->layout('layout', ['title' => 'Confirme seu e-mail']);
?>
<?php $this->start('body') ?>

    <p>Olá, <?= htmlspecialchars($name) ?>!</p>

    <p>Falta pouco para começar a usar o Moneta. Clique no botão abaixo para confirmar seu e-mail e ativar sua conta:</p>

    <p style="margin: 24px 0;">
        <a href="<?= htmlspecialchars($verifyUrl) ?>" style="background:#0D3B36; color:#ffffff; padding: 12px 24px; border-radius: 6px; text-decoration:none; font-weight:bold; display:inline-block;">
            Confirmar e-mail
        </a>
    </p>

    <p style="color:#6c757d; font-size: 12px;">
        Se você não criou uma conta no Moneta, pode ignorar este e-mail com segurança.
    </p>

<?php $this->stop() ?>