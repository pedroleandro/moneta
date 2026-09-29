<?php

$router->get("/emprestimos", "LoanController@index", "loans.index");
$router->get("/emprestimos/novo", "LoanController@create", "loans.create");
$router->post("/emprestimos/novo", "LoanController@store", "loans.store");
$router->get("/emprestimos/{id}/editar", "LoanController@edit", "loans.edit");
$router->post("/emprestimos/{id}/editar", "LoanController@update", "loans.update");
$router->post("/emprestimos/{id}/excluir", "LoanController@destroy", "loans.destroy");

$router->post("/emprestimos/{id}/devolucoes", "LoanController@storePayment", "loans.payments.store");
$router->post("/emprestimos/{id}/devolucoes/{paymentId}/excluir", "LoanController@destroyPayment", "loans.payments.destroy");
