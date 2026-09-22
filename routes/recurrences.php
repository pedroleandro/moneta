<?php

$router->group(null);

$router->get("/recorrencias", "RecurrenceController@index");
$router->get("/recorrencias/nova", "RecurrenceController@create");
$router->post("/recorrencias/nova", "RecurrenceController@store");
$router->get("/recorrencias/{id}/editar", "RecurrenceController@edit");
$router->post("/recorrencias/{id}/editar", "RecurrenceController@update");
$router->post("/recorrencias/{id}/excluir", "RecurrenceController@destroy");
