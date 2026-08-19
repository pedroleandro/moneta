<?php

namespace App\Core;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    private \PDO $connection;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT payload FROM sessions WHERE id = :id"
            );
            $statement->execute(["id" => $id]);
            $row = $statement->fetch();

            return $row ? $row->payload : "";
        } catch (\Throwable $exception) {
            Logger::error("Falha ao ler sessão do banco", [
                "session_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            return "";
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $session = new Session();
            $userId = $session->get("auth")->id ?? null;

            $statement = $this->connection->prepare(
                "INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                 VALUES (:id, :user_id, :ip_address, :user_agent, :payload, :last_activity)
                 ON DUPLICATE KEY UPDATE
                    user_id = VALUES(user_id),
                    ip_address = VALUES(ip_address),
                    user_agent = VALUES(user_agent),
                    payload = VALUES(payload),
                    last_activity = VALUES(last_activity)"
            );

            return $statement->execute([
                "id" => $id,
                "user_id" => $userId,
                "ip_address" => substr($_SERVER["REMOTE_ADDR"] ?? "", 0, 45),
                "user_agent" => substr($_SERVER["HTTP_USER_AGENT"] ?? "", 0, 255),
                "payload" => $data,
                "last_activity" => time(),
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao gravar sessão no banco", [
                "session_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $statement = $this->connection->prepare("DELETE FROM sessions WHERE id = :id");
            return $statement->execute(["id" => $id]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao destruir sessão no banco", [
                "session_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $statement = $this->connection->prepare(
                "DELETE FROM sessions WHERE last_activity < :cutoff"
            );
            $statement->execute(["cutoff" => time() - $max_lifetime]);

            return $statement->rowCount();
        } catch (\Throwable $exception) {
            Logger::error("Falha na faxina (gc) de sessões", [
                "exception" => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public static function destroyAllForUser(int $userId): void
    {
        try {
            $connection = Connection::getInstance();
            $statement = $connection->prepare("DELETE FROM sessions WHERE user_id = :user_id");
            $statement->execute(["user_id" => $userId]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao derrubar sessões do usuário", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
        }
    }

    public static function listForUser(int $userId): array
    {
        try {
            $connection = Connection::getInstance();
            $statement = $connection->prepare(
                "SELECT id, ip_address, user_agent, last_activity, created_at
                 FROM sessions
                 WHERE user_id = :user_id
                 ORDER BY last_activity DESC"
            );
            $statement->execute(["user_id" => $userId]);

            return $statement->fetchAll();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar sessões do usuário", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            return [];
        }
    }
}