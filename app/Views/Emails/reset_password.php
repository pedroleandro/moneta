<?php
/**
 * Variáveis esperadas:
 *   string $name, $resetUrl
 */
$this->layout('layout', ['title' => 'Redefinição de senha']);
?>
<?php $this->start('body') ?>

    <p>Olá, <?= htmlspecialchars($name) ?>!</p>

    <p>Recebemos um pedido para redefinir a senha da sua conta Moneta. Clique no botão abaixo para escolher uma nova senha:</p>

    <p style="margin: 24px 0;">
        <a href="<?= htmlspecialchars($resetUrl) ?>" style="background:#0D3B36; color:#ffffff; padding: 12px 24px; border-radius: 6px; text-decoration:none; font-weight:bold; display:inline-block;">
            Redefinir senha
        </a>
    </p>

    <p style="color:#6c757d; font-size: 12px;">
        Este link expira em 2 horas. Se você não pediu essa redefinição, pode ignorar este e-mail com segurança —
        sua senha atual continua válida.
    </p>

<?php $this->stop() ?>