<?php

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

class Logger
{
    private static ?MonologLogger $instance = null;

    private static function instance(): MonologLogger
    {
        if (self::$instance === null) {
            $logger = new MonologLogger("moneta");

            $handler = new RotatingFileHandler(
                __DIR__ . "/../../storage/logs/app.log",
                14,
                Level::Debug
            );

            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context%\n",
                "Y-m-d H:i:s"
            );
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            self::$instance = $logger;
        }

        return self::$instance;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::instance()->debug($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::instance()->info($message, $context);
    }

    public static function notice(string $message, array $context = []): void
    {
        self::instance()->notice($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::instance()->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::instance()->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::instance()->critical($message, $context);
    }
}