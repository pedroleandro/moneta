<?php

$router->group(null);

$router->get("/transferencias", "AccountTransferController@index");
$router->get("/transferencias/nova", "AccountTransferController@create");
$router->post("/transferencias/nova", "AccountTransferController@store");
$router->post("/transferencias/{id}/excluir", "AccountTransferController@destroy");
