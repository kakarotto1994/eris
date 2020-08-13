<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;

// Start Categorias 
$app->get('/eris/categories', function (){
	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$categories,

	]);

});

$app->get('/eris/categories/create', function (){

	User::verifyLogin();
	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post('/eris/categories/create', function (){
	User::verifyLogin();

	$category = new Category();
	
	$category->setData($_POST);

	$category->save();

	header("Location: /eris/categories");
	exit;

});

$app->get('/eris/categories/:idcategory/delete', function ($idcategory){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /eris/categories");
	exit;

});


$app->get('/eris/categories/:idcategory', function ($idcategory){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update", array(
		"category"=>$category->getValues()
	));

});


$app->post('/eris/categories/:idcategory', function ($idcategory){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /eris/categories");

	exit;

});

// end Categorias admin

