<?php

$router->group(null);

$router->get("/parcelamentos", "InstallmentPurchaseController@index");
$router->get("/parcelamentos/novo", "InstallmentPurchaseController@create");
$router->post("/parcelamentos/novo", "InstallmentPurchaseController@store");
