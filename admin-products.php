<?php

use \Slim\Slim;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Start Products Admin

$app->get('/eris/products', function (){
	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);

});

$app->get('/eris/products/create', function (){
    User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("products-create");

});

$app->post('/eris/products/create', function (){
    User::verifyLogin();

    $products = new Product();

    $products->setData($_POST);

    var_dump($products);

    $products->save();

    header("Location: /eris/products");
    exit;

});

$app->get('/eris/products/:idproduct', function ($idproduct){
    User::verifyLogin();
    
    $product = new Product();

    $product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", array(
        "product"=>$product->getValues()
    ));

});

$app->post('/eris/products/:idproduct', function ($idproduct){
    User::verifyLogin();
    
    $product = new Product();

    $product->get((int)$idproduct);

    $product->setData($_POST);

    $product->save();

    if($_FILES["file"]["name"] != "") $product->setPhoto($_FILES["file"]);

    header("Location: /eris/products");
    exit;

});

$app->get('/eris/products/:idproduct/delete', function ($idproduct){
    User::verifyLogin();
    
    $product = new Product();

    $product->get((int)$idproduct);

    $product->delete();
    header("Location: /eris/products");
    exit;

});