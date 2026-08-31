<?php

namespace App\Core;

class EmailLog
{
    public static function record(
        string  $toEmail,
        string  $subject,
        string  $type,
        string  $status,
        ?int    $userId = null,
        ?string $errorMessage = null
    ): void
    {
        try {
            $connection = Connection::getInstance();
            $statement = $connection->prepare(
                "INSERT INTO email_logs (user_id, to_email, subject, type, status, error_message, created_at)
                 VALUES (:user_id, :to_email, :subject, :type, :status, :error_message, :created_at)"
            );
            $statement->execute([
                "user_id" => $userId,
                "to_email" => $toEmail,
                "subject" => $subject,
                "type" => $type,
                "status" => $status,
                "error_message" => $errorMessage,
                "created_at" => (new \DateTime("now", new \DateTimeZone("America/Sao_Paulo")))->format("Y-m-d H:i:s"),
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao registrar email log", [
                "to" => $toEmail,
                "type" => $type,
                "exception" => $exception->getMessage(),
            ]);
        }
    }
}