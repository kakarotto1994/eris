<?php

use \Slim\Slim;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;

//acesso a pagina inicial do modulo de administracao
$app->get('/eris', function() {

	User::verifyLogin();

	$user = User::getFromSession();

	$page = new PageAdmin();

	$page->setTpl("index", [
		"user"=>$user->getValues()
	]);

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

//Esqueci a senha

$app->get("/eris/forgot", function() {
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});

$app->post("/eris/forgot", function() {

	$user = User::getForgot($_POST["email"]);

	header("Location: /eris/forgot/sent");
	exit;

});

$app->get("/eris/forgot/sent", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/eris/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/eris/forgot/reset", function() {



	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>10
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});


?>