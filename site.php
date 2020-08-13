<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Category;

//rota index php
$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index");

});

//start categoria Site Eris

$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>[]
	]);

});

//end categoria Site Eri

?>