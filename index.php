<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//rota index php
$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index");

});

//acesso a pagina inicial do modulo de administracao
$app->get('/eris', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

// acesso a pagina de login
$app->get("/eris/login", function() {
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");
});

//ação logar
$app->post("/eris/login", function() {
	
	User::login($_POST["login"], $_POST["password"]);
	header("Location: /eris");
	exit;

});

//logout
$app->get("/eris/logout", function() {

	User::logout();

	header("Location: /eris/login");
	exit;

});

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


$app->run();

 ?>