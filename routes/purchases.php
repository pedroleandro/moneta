<?php

$router->group(null);

$router->get("/parcelamentos", "InstallmentPurchaseController@index");
$router->get("/parcelamentos/novo", "InstallmentPurchaseController@create");
$router->post("/parcelamentos/novo", "InstallmentPurchaseController@store");
$router->get("/parcelamentos/{id}/editar", "InstallmentPurchaseController@edit");
$router->post("/parcelamentos/{id}/editar", "InstallmentPurchaseController@update");
$router->post("/parcelamentos/{id}/excluir", "InstallmentPurchaseController@destroy");
