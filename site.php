<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

//rota index php
$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

//start categoria Site Eris

$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);

});

//end categoria Site Eri

?>