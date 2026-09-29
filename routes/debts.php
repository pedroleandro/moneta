<?php

$router->get("/fiado", "DebtController@index", "debts.index");
$router->get("/fiado/novo", "DebtController@create", "debts.create");
$router->post("/fiado/novo", "DebtController@store", "debts.store");
$router->get("/fiado/{id}/editar", "DebtController@edit", "debts.edit");
$router->post("/fiado/{id}/editar", "DebtController@update", "debts.update");
$router->post("/fiado/{id}/excluir", "DebtController@destroy", "debts.destroy");

$router->post("/fiado/{id}/pagamentos", "DebtController@storePayment", "debts.payments.store");
$router->post("/fiado/{id}/pagamentos/{paymentId}/excluir", "DebtController@destroyPayment", "debts.payments.destroy");
