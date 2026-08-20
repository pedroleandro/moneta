<?php

$router->group(null);

$router->get("/categorias", "CategoryController@index");
$router->get("/categorias/nova", "CategoryController@create");
$router->post("/categorias/nova", "CategoryController@store");
$router->get("/categorias/{id}/editar", "CategoryController@edit");
$router->post("/categorias/{id}/editar", "CategoryController@update");
$router->post("/categorias/{id}/excluir", "CategoryController@destroy");
