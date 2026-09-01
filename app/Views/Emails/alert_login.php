<?php
$this->layout('layout', ['title' => 'Novo acesso detectado']);
?>
<?php $this->start('body') ?>

<p>Identificamos um login na sua conta Moneta a partir de um dispositivo que não reconhecíamos:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 16px 0; font-size: 14px;">
    <tr>
        <td style="color:#6c757d; padding: 4px 0;">Dispositivo</td>
        <td style="padding: 4px 0;"><strong><?= htmlspecialchars($device) ?></strong></td>
    </tr>
    <tr>
        <td style="color:#6c757d; padding: 4px 0;">Localização</td>
        <td style="padding: 4px 0;"><strong><?= htmlspecialchars($locationText) ?></strong></td>
    </tr>
    <tr>
        <td style="color:#6c757d; padding: 4px 0;">Data e hora</td>
        <td style="padding: 4px 0;"><strong><?= htmlspecialchars($when) ?></strong></td>
    </tr>
    <tr>
        <td style="color:#6c757d; padding: 4px 0;">Endereço IP</td>
        <td style="padding: 4px 0;"><strong><?= htmlspecialchars($ipAddress) ?></strong></td>
    </tr>
</table>

<p>Foi você?</p>

<p style="margin: 24px 0;">
    <a href="<?= htmlspecialchars($confirmUrl) ?>" style="background:#0D3B36; color:#ffffff; padding: 12px 24px; border-radius: 6px; text-decoration:none; font-weight:bold; display:inline-block;">
        Verificar este acesso
    </a>
</p>

<p style="color:#6c757d; font-size: 12px;">
    Se não reconhece esse acesso, clique no botão acima e selecione "Não fui eu" —
    vamos encerrar todas as sessões abertas e pedir uma nova senha imediatamente.
</p>

<?php $this->stop() ?>
