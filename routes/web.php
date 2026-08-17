<?php

use CoffeeCode\Router\Router;

$router = new Router(APP_URL, "@");
$router->namespace("app\Controllers");


/*
|--------------------------------------------------------------------------
| Rotas da Web
|--------------------------------------------------------------------------
*/
$router->get("/", "WebController@index");


/*
|--------------------------------------------------------------------------
| Rotas de Erro
|--------------------------------------------------------------------------
*/
$router->group(null);
$router->get("/erro/{errorCode}", "ErrorController@index");


$router->dispatch();


if ($router->error()) {
    redirect("/erro/{$router->error()}");
}