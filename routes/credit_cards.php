<?php

$router->group(null);

$router->get("/cartoes", "CreditCardController@index");
$router->get("/cartoes/novo", "CreditCardController@create");
$router->post("/cartoes/novo", "CreditCardController@store");
$router->get("/cartoes/{id}/editar", "CreditCardController@edit");
$router->post("/cartoes/{id}/editar", "CreditCardController@update");
$router->post("/cartoes/{id}/excluir", "CreditCardController@destroy");
