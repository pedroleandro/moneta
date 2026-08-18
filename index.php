<?php

require __DIR__ . "/vendor/autoload.php";

set_exception_handler(function (\Throwable $exception) {
    \App\Core\Logger::critical("Exceção não tratada", [
        "message" => $exception->getMessage(),
        "file" => $exception->getFile(),
        "line" => $exception->getLine(),
    ]);

    http_response_code(500);
    echo "Ocorreu um erro. Tente novamente mais tarde.";
});

use App\Core\Session;

new Session();

require __DIR__ . "/routes/web.php";