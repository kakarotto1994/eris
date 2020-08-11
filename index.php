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

$app->post("/eris/login", function() {
	
	User::login($_POST["login"], $_POST["password"]);
	header("Location: /eris");
	exit;

});

$app->get("/eris/logout", function() {

	User::logout();

	header("Location: /eris/login");
	exit;

});

$app->run();

 ?>