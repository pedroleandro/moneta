<?php

namespace App\Core;

use DateTimeZone;

class AuditLog
{
    public static function record(string $event, ?int $userId = null, array $metadata = [], ?string $description = null): void
    {
        try {
            $connection = Connection::getInstance();

            $statement = $connection->prepare(
                "INSERT INTO audit_logs (user_id, event, description, ip_address, user_agent, metadata, created_at)
                 VALUES (:user_id, :event, :description, :ip_address, :user_agent, :metadata, :created_at)"
            );

            $statement->execute([
                "user_id" => $userId,
                "event" => $event,
                "description" => $description ?? LogEvent::label($event),
                "ip_address" => self::clientIp(),
                "user_agent" => substr($_SERVER["HTTP_USER_AGENT"] ?? "", 0, 255),
                "metadata" => $metadata ? json_encode($metadata) : null,
                "created_at" => (new \DateTime("now", new DateTimeZone("America/Sao_Paulo")))->format("Y-m-d H:i:s")
            ]);
        } catch (\Throwable $exception) {
            // Falha ao gravar log de auditoria não pode derrubar a aplicação.
            error_log("Falha ao registrar audit log [{$event}]: " . $exception->getMessage());
        }
    }

    private static function clientIp(): string
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"] ?? $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
    }
}