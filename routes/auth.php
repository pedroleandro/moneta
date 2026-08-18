<?php

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/

$router->group(null);
$router->get("/entrar", "AuthController@index");
$router->post("/entrar", "AuthController@authenticate");

$router->post("/sair", "AuthController@logout");

$router->get("/cadastrar", "AuthController@create");
$router->post("/cadastrar", "AuthController@store");
$router->get("/cadastrar/sucesso", "AuthController@storeSuccess");

$router->get("/esqueceu-senha", "AuthController@forgotPassword");
$router->post("/redefinir-senha", "AuthController@sendResetLink");
$router->get("/redefinir-senha/sucesso", "AuthController@sendResetLinkSuccess");

$router->get("/resetar-senha/{token}", "AuthController@resetPassword");
$router->post("/resetar-senha", "AuthController@updatePassword");

$router->get("/verificar-email/{token}", "AuthController@verifyEmail");
$router->post("/reenviar-verificacao", "AuthController@resendVerification");

/*
|--------------------------------------------------------------------------
| Rotas Autenticadas
|--------------------------------------------------------------------------
*/
$router->get("/dashboard", "DashboardController@index");