<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

define("APP_URL", $_ENV['APP_URL'] ?? "http://localhost:8009");
define("APP_NAME", $_ENV['APP_NAME'] ?? "MONETA - Sistema Web de Controle Financeiro Pessoal");

define("APP_TIMEZONE", $_ENV['APP_TIMEZONE'] ?? "America/Sao_Paulo");

define("DB_CONNECTION", $_ENV['DB_CONNECTION'] ?? "mysql");
define("DB_HOST", $_ENV['DB_HOST'] ?? "localhost");
define("DB_PORT", $_ENV['DB_PORT'] ?? "3306");
define("DB_DATABASE", $_ENV['DB_DATABASE'] ?? "db");
define("DB_USERNAME", $_ENV['DB_USERNAME'] ?? "user");
define("DB_PASSWORD", $_ENV['DB_PASSWORD'] ?? "password");
define("DB_CHARSET", $_ENV['DB_CHARSET'] ?? "utf8mb4");

define("APP_ENV", $_ENV['APP_ENV'] ?? "production");

define("EMAIL_SEND", $_ENV['EMAIL_SEND'] ?? "contato@moneta.com.br");
define("EMAIL_NAME", $_ENV['EMAIL_NAME'] ?? "Equipe Técnica do MONETA");

// Em local, usa Mailpit (SMTP falso, sem enviar de verdade nem gastar créditos)
define("MAIL_DRIVER", $_ENV['MAIL_DRIVER'] ?? (APP_ENV === "local" ? "mailpit" : "smtp"));
define("MAIL_HOST", $_ENV['MAIL_HOST'] ?? "mailpit");
define("MAIL_PORT", $_ENV['MAIL_PORT'] ?? 1025);
define("MAIL_USERNAME", $_ENV['MAIL_USERNAME'] ?? "");
define("MAIL_PASSWORD", $_ENV['MAIL_PASSWORD'] ?? "");
define("MAIL_ENCRYPTION", $_ENV['MAIL_ENCRYPTION'] ?? "tls"); // tls (587) ou ssl (465)

define("APP_DEVELOPER", $_ENV['APP_DEVELOPER'] ?? "Pedro Leandro");

define("TICKET_MAX_ATTACHMENTS", $_ENV['TICKET_MAX_ATTACHMENTS'] ?? 10);
define("UPLOAD_MAX_SIZE", $_ENV['UPLOAD_MAX_SIZE'] ?? 5 * 1024 * 1024);
const UPLOAD_PATH = __DIR__ . "/../storage/uploads";