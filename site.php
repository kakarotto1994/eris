<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;

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

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i = 1; $i <= $pagination["pages"]; $i++) {

		array_push($pages, [
			"link"=>"/categories/".$category->getidcategory()."?page=".$i,
			"page"=>$i
		]);

	}

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);

});

//end categoria Site Eri

$app->get('/products/:desurl', function($desurl) {

	$product = new Product();

	$url = $product->getFromUrl($desurl);

	$page = new Page();

	$categories = $product->getCategories();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$categories
	]);

});

//carrinho de compra

$app->get('/cart', function() {

	$product = new Product();

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart");

});



?>