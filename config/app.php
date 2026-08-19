<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

define("APP_URL", $_ENV['APP_URL'] ?? "http://localhost:8009");
define("APP_NAME", $_ENV['APP_NAME'] ?? "MONETA - Sistema Web de Controle Financeiro Pessoal");

define("APP_TIMEZONE", $_ENV['APP_TIMEZONE'] ?? "America/Sao_Paulo");
date_default_timezone_set(APP_TIMEZONE);

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

define("MAIL_DRIVER", $_ENV['MAIL_DRIVER'] ?? (APP_ENV === "local" ? "mailpit" : "smtp"));
define("MAIL_HOST", $_ENV['MAIL_HOST'] ?? "mailpit");
define("MAIL_PORT", $_ENV['MAIL_PORT'] ?? 1025);
define("MAIL_USERNAME", $_ENV['MAIL_USERNAME'] ?? "");
define("MAIL_PASSWORD", $_ENV['MAIL_PASSWORD'] ?? "");
define("MAIL_ENCRYPTION", $_ENV['MAIL_ENCRYPTION'] ?? "tls");

define("REDIS_HOST", $_ENV['REDIS_HOST'] ?? "redis");
define("REDIS_PORT", $_ENV['REDIS_PORT'] ?? 6379);

define("GOOGLE_CLIENT_ID", $_ENV['GOOGLE_CLIENT_ID'] ?? "");
define("GOOGLE_CLIENT_SECRET", $_ENV['GOOGLE_CLIENT_SECRET'] ?? "");

define("FACEBOOK_CLIENT_ID", $_ENV['FACEBOOK_CLIENT_ID'] ?? "");
define("FACEBOOK_CLIENT_SECRET", $_ENV['FACEBOOK_CLIENT_SECRET'] ?? "");

define("TURNSTILE_SITE_KEY", $_ENV['TURNSTILE_SITE_KEY'] ?? "");
define("TURNSTILE_SECRET_KEY", $_ENV['TURNSTILE_SECRET_KEY'] ?? "");

define("APP_DEVELOPER", $_ENV['APP_DEVELOPER'] ?? "Pedro Leandro");

define("AUTH_MAX_ATTEMPTS", (int)($_ENV['AUTH_MAX_ATTEMPTS'] ?? 5));
define("AUTH_LOCKOUT_MINUTES", (int)($_ENV['AUTH_LOCKOUT_MINUTES'] ?? 15));

define("TICKET_MAX_ATTACHMENTS", $_ENV['TICKET_MAX_ATTACHMENTS'] ?? 10);
define("UPLOAD_MAX_SIZE", $_ENV['UPLOAD_MAX_SIZE'] ?? 5 * 1024 * 1024);
const UPLOAD_PATH = __DIR__ . "/../storage/uploads";