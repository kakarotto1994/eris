<?php

use \Slim\Slim;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

////vizualiza usuarios CRUD user
$app->get("/eris/users", function(){

	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));

});

//cria usuarios CRUD user
$app->get("/eris/users/create", function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//deletar usuario
$app->get("/eris/users/:iduser/delete", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /eris/users");
	exit;

});

//atualiza usuarios CRUD user
$app->get("/eris/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//Salvar criacao do usuario

$app->post("/eris/users/create", function(){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /eris/users");
	exit;

});

//atualiza usuarios CRUD user
$app->post("/eris/users/:iduser", function($iduser) {

	User::verifyLogin();
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();
	header("Location: /eris/users");
	exit;

});


?>