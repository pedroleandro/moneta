<?php

namespace App\Core;

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
                "created_at" => (new \DateTime("now", new \DateTimeZone("America/Sao_Paulo")))->format("Y-m-d H:i:s")
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao registrar audit log", [
                "event" => $event,
                "exception" => $exception->getMessage(),
            ]);
        }
    }

    private static function clientIp(): string
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"] ?? $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
    }

    public static function hasPriorSuccessfulLogin(int $userId, string $ip): bool
    {
        try {
            $connection = Connection::getInstance();

            $statement = $connection->prepare(
                "SELECT COUNT(*) AS total
                 FROM audit_logs
                 WHERE user_id = :user_id
                   AND event = :event
                   AND ip_address = :ip"
            );

            $statement->execute([
                "user_id" => $userId,
                "event" => LogEvent::LOGIN_SUCCESS,
                "ip" => $ip,
            ]);

            return (int)$statement->fetch()->total > 0;
        } catch (\Throwable $exception) {
            Logger::warning("Falha ao checar histórico de login", [
                "exception" => $exception->getMessage(),
            ]);
            return true;
        }
    }
}