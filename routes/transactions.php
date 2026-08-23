<?php

$router->group(null);

$router->get("/lancamentos", "TransactionController@index");
$router->get("/lancamentos/novo", "TransactionController@create");
$router->post("/lancamentos/novo", "TransactionController@store");
$router->get("/lancamentos/{id}/editar", "TransactionController@edit");
$router->post("/lancamentos/{id}/editar", "TransactionController@update");
$router->post("/lancamentos/{id}/excluir", "TransactionController@destroy");
