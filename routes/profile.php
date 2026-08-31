<?php

$router->group(null);

$router->get("/perfil", "ProfileController@index");
$router->post("/perfil/dados", "ProfileController@updatePersonalData");
$router->post("/perfil/senha", "ProfileController@updatePassword");
$router->post("/perfil/social/desvincular", "ProfileController@unlinkSocialAccount");
$router->post("/perfil/sessoes/encerrar", "ProfileController@destroySession");
$router->post("/perfil/sessoes/encerrar-outras", "ProfileController@destroyOtherSessions");
$router->post("/perfil/avatar", "ProfileController@updateAvatar");
