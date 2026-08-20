<?php

$router->group(null);

$router->get("/contas", "BankAccountController@index");
$router->get("/contas/nova", "BankAccountController@create");
$router->post("/contas/nova", "BankAccountController@store");
$router->get("/contas/{id}/editar", "BankAccountController@edit");
$router->post("/contas/{id}/editar", "BankAccountController@update");
$router->post("/contas/{id}/excluir", "BankAccountController@destroy");
