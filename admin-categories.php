<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

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

$app->get("/eris/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);

});

$app->get("/eris/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /eris/categories/$idcategory/products");
	exit;

});

$app->get("/eris/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /eris/categories/$idcategory/products");
	exit;

});


///eris/categories/{$category.idcategory}/products/{$value.idproduct}/add