<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Moneta') ?></title>
</head>
<body style="margin:0; padding:0; background-color:#0D3B36; font-family: Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#0D3B36; padding: 40px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width: 480px; background-color:#ffffff; border-radius: 12px; overflow: hidden;">
                <!-- Cabeçalho -->
                <tr>
                    <td style="background-color:#0D3B36; padding: 24px 32px; text-align:center;">
                        <div style="background-color:#ffffff; display:inline-block; padding: 10px 20px; border-radius: 8px;">
                            <img src="<?= assets('/images/moneta_logo_horizontal.png') ?>" alt="Moneta" style="height: 32px; display:block;">
                        </div>
                    </td>
                </tr>
                <!-- Conteúdo -->
                <tr>
                    <td style="padding: 32px;">
                        <h2 style="margin: 0 0 16px 0; color:#0D3B36; font-size: 20px;">
                            <?= htmlspecialchars($title ?? '') ?>
                        </h2>
                        <div style="color:#333333; font-size: 14px; line-height: 1.6;">
                            <?= $this->section('body') ?>
                        </div>
                    </td>
                </tr>
                <!-- Rodapé -->
                <tr>
                    <td style="background-color:#f5f5f5; padding: 24px 32px; text-align:center;">
                        <p style="margin:0 0 8px 0; color:#6c757d; font-size:12px;">
                            Este e-mail foi enviado
                            por <?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Moneta' ?>.
                        </p>
                        <p style="margin:0 0 8px 0; color:#6c757d; font-size:12px;">
                            <a href="<?= function_exists('url') ? url('/termos') : '#' ?>"
                               style="color:#C9974E; text-decoration:none;">Termos de Uso e Política de Privacidade</a>
                        </p>
                        <p style="margin:0; color:#adb5bd; font-size:11px;">
                            &copy; <?= date('Y') ?> Moneta. Todos os direitos reservados.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>