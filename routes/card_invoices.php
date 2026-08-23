<?php

$router->group(null);

$router->get("/faturas", "CardInvoiceController@index");
$router->get("/faturas/{id}", "CardInvoiceController@show");
$router->post("/faturas/{id}/pagar", "CardInvoiceController@pay");
