<?php

$router->group(null);

$router->get("/pessoas-cartao", "CardUserController@index");
$router->get("/pessoas-cartao/nova", "CardUserController@create");
$router->post("/pessoas-cartao/nova", "CardUserController@store");
$router->get("/pessoas-cartao/{id}/editar", "CardUserController@edit");
$router->post("/pessoas-cartao/{id}/editar", "CardUserController@update");
$router->post("/pessoas-cartao/{id}/excluir", "CardUserController@destroy");
