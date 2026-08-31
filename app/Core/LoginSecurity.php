<?php

namespace App\Core;

use Random\RandomException;

class LoginSecurity
{
    public static function checkAndNotify(
        int $userId,
        string $email,
        string $name,
        string $ipAddress,
        string $userAgent
    ): void {
        $connection = Connection::getInstance();
        $deviceHash = self::computeDeviceHash($userAgent, $ipAddress);

        $statement = $connection->prepare(
            "SELECT id FROM known_devices WHERE user_id = :user_id AND device_hash = :hash LIMIT 1"
        );
        $statement->execute(["user_id" => $userId, "hash" => $deviceHash]);
        $existing = $statement->fetch();

        if ($existing) {
            $update = $connection->prepare(
                "UPDATE known_devices SET last_seen_at = NOW(), ip_address = :ip WHERE id = :id"
            );
            $update->execute(["ip" => $ipAddress, "id" => $existing->id]);
            return;
        }

        $location = self::lookupLocation($ipAddress);

        $insert = $connection->prepare(
            "INSERT INTO known_devices (user_id, device_hash, user_agent, ip_address, location)
             VALUES (:user_id, :hash, :agent, :ip, :location)"
        );
        $insert->execute([
            "user_id" => $userId,
            "hash" => $deviceHash,
            "agent" => substr($userAgent, 0, 255),
            "ip" => $ipAddress,
            "location" => $location,
        ]);

        $token = bin2hex(random_bytes(32));

        $tokenInsert = $connection->prepare(
            "INSERT INTO login_verification_tokens (user_id, token, ip_address, user_agent, location, expires_at)
             VALUES (:user_id, :token, :ip, :agent, :location, DATE_ADD(NOW(), INTERVAL 7 DAY))"
        );
        $tokenInsert->execute([
            "user_id" => $userId,
            "token" => $token,
            "ip" => $ipAddress,
            "agent" => substr($userAgent, 0, 255),
            "location" => $location,
        ]);

        self::sendAlertEmail($userId, $email, $name, $token, $ipAddress, $userAgent, $location);
    }

    /**
     * @throws RandomException
     */
    public static function reportSuspicious(string $token): ?string
    {
        $connection = Connection::getInstance();

        $statement = $connection->prepare(
            "SELECT * FROM login_verification_tokens
             WHERE token = :token AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1"
        );
        $statement->execute(["token" => $token]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        $connection->prepare(
            "UPDATE login_verification_tokens SET used_at = NOW(), reported_suspicious_at = NOW() WHERE id = :id"
        )->execute(["id" => $row->id]);

        \App\Core\DatabaseSessionHandler::destroyAllForUser($row->user_id);

        $currentAuth = (new \App\Core\Session())->get("auth");
        if ($currentAuth && (int)($currentAuth->id ?? 0) === (int)$row->user_id) {
            (new \App\Core\Session())->destroy();
        }

        $user = \App\Models\User::find($row->user_id);
        $resetToken = $user->setResetToken();
        $user->save();

        AuditLog::record(LogEvent::SUSPICIOUS_LOGIN_REPORTED, $row->user_id, [
            "verification_token_id" => $row->id,
            "ip_address" => $row->ip_address,
        ]);

        return $resetToken;
    }

    public static function confirmLegit(string $token): bool
    {
        $connection = Connection::getInstance();

        $statement = $connection->prepare(
            "UPDATE login_verification_tokens SET used_at = NOW()
             WHERE token = :token AND used_at IS NULL AND expires_at > NOW()"
        );
        $statement->execute(["token" => $token]);

        return $statement->rowCount() > 0;
    }

    public static function findToken(string $token): ?object
    {
        $connection = Connection::getInstance();

        $statement = $connection->prepare(
            "SELECT * FROM login_verification_tokens
             WHERE token = :token AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1"
        );
        $statement->execute(["token" => $token]);

        $row = $statement->fetch();

        return $row ?: null;
    }

    private static function computeDeviceHash(string $userAgent, string $ipAddress): string
    {
        $ipPrefix = implode(".", array_slice(explode(".", $ipAddress), 0, 3));

        return hash("sha256", $userAgent . "|" . $ipPrefix);
    }

    private static function lookupLocation(string $ipAddress): ?string
    {
        $isLocal = in_array($ipAddress, ["127.0.0.1", "::1"], true)
            || str_starts_with($ipAddress, "192.168.")
            || str_starts_with($ipAddress, "10.");

        if ($isLocal) {
            return "Rede local";
        }

        try {
            $context = stream_context_create(["http" => ["timeout" => 3]]);
            $response = @file_get_contents(
                "http://ip-api.com/json/{$ipAddress}?fields=city,regionName,country&lang=pt-BR",
                false,
                $context
            );

            if (!$response) {
                return null;
            }

            $data = json_decode($response, true);

            if (empty($data["city"])) {
                return null;
            }

            return trim("{$data['city']}, {$data['regionName']} — {$data['country']}");
        } catch (\Throwable) {
            return null;
        }
    }

    private static function parseDevice(string $userAgent): string
    {
        $browser = "Navegador desconhecido";
        $os = "Sistema desconhecido";

        if (str_contains($userAgent, "Edg/")) {
            $browser = "Microsoft Edge";
        } elseif (str_contains($userAgent, "Chrome/")) {
            $browser = "Google Chrome";
        } elseif (str_contains($userAgent, "Firefox/")) {
            $browser = "Mozilla Firefox";
        } elseif (str_contains($userAgent, "Safari/")) {
            $browser = "Safari";
        }

        if (str_contains($userAgent, "Windows")) {
            $os = "Windows";
        } elseif (str_contains($userAgent, "Mac OS")) {
            $os = "macOS";
        } elseif (str_contains($userAgent, "Android")) {
            $os = "Android";
        } elseif (str_contains($userAgent, "iPhone") || str_contains($userAgent, "iPad")) {
            $os = "iOS";
        } elseif (str_contains($userAgent, "Linux")) {
            $os = "Linux";
        }

        return "{$browser} em {$os}";
    }

    private static function sendAlertEmail(
        int $userId,
        string $email,
        string $name,
        string $token,
        string $ipAddress,
        string $userAgent,
        ?string $location
    ): void {
        $device = self::parseDevice($userAgent);
        $when = date("d/m/Y \à\s H:i");
        $locationText = $location ?: "Localização não identificada";

        $confirmUrl = url("/seguranca/verificar-login/{$token}");

        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto;'>
                <h2 style='color: #0D3B36;'>Novo acesso detectado</h2>
                <p>Identificamos um login na sua conta Moneta a partir de um dispositivo que não reconhecíamos:</p>
                <table style='width: 100%; margin: 16px 0; font-size: 14px;'>
                    <tr><td style='color: #6c757d; padding: 4px 0;'>Dispositivo</td><td><strong>{$device}</strong></td></tr>
                    <tr><td style='color: #6c757d; padding: 4px 0;'>Localização</td><td><strong>{$locationText}</strong></td></tr>
                    <tr><td style='color: #6c757d; padding: 4px 0;'>Data e hora</td><td><strong>{$when}</strong></td></tr>
                    <tr><td style='color: #6c757d; padding: 4px 0;'>Endereço IP</td><td><strong>{$ipAddress}</strong></td></tr>
                </table>
                <p>Foi você?</p>
                <p style='margin: 24px 0;'>
                    <a href='{$confirmUrl}' style='background: #0D3B36; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;'>
                        Verificar este acesso
                    </a>
                </p>
                <p style='color: #6c757d; font-size: 12px;'>
                    Se não reconhece esse acesso, clique no botão acima e selecione
                    \"Não fui eu\" — vamos encerrar todas as sessões abertas e pedir
                    uma nova senha imediatamente.
                </p>
            </div>
        ";

        try {
            (new Email())
                ->bootstrap("Novo acesso detectado — Moneta", $body, $email, $name, "login_alert", $userId)
                ->send();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao enviar e-mail de alerta de login", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
        }
    }
}