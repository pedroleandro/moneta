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
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/auth.php";


/*
|--------------------------------------------------------------------------
| Rotas de Categorias
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/categories.php";

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