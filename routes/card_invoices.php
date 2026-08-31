<?php

$router->group(null);

$router->get("/faturas", "CardInvoiceController@index");
$router->get("/faturas/{id}", "CardInvoiceController@show");
$router->post("/faturas/{id}/pagar", "CardInvoiceController@pay");
$router->get("/faturas/{id}/pagamentos/{paymentId}/editar", "CardInvoiceController@editPayment");
$router->post("/faturas/{id}/pagamentos/{paymentId}/editar", "CardInvoiceController@updatePayment");
$router->post("/faturas/{id}/pagamentos/{paymentId}/excluir", "CardInvoiceController@deletePayment");
