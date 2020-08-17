<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>$cart->getMsgError()
	]);

});

// adicionar produto ao carrinho

$app->get('/cart/:idproduct/add', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] : 1;

	for($i = 0; $i < $qtd; $i++) {

		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;

});

$app->get('/cart/:idproduct/minus', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

$app->get('/cart/:idproduct/remove', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

$app->post('/cart/freight', function() {

	$cart = Cart::getFromSession();
	$cart->setFreight($_POST["zipcode"]);

	header("Location: /cart");
	exit;

});

//Finalizar a compra

$app->get("/checkout", function() {

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl('checkout', [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()

	]);

});

// Login no site
$app->get("/login", function() {

	$page = new Page();


	$page->setTpl("login", [
		"error"=>User::getError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=> '', 'phone'=>'']
	]);

});


$app->post("/login", function() {

	Try {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /cart");

	} catch(Exception $e) {

		header("Location: /login");

		User::setError($e->getMessage());

	}

	exit;

});

//Logout site

$app->get("/logout", function() {

	User::logout();

	header("Location: /");
	exit;

});

$app->post("/register", function() {

	$_SESSION['registerValues'] = $_POST;

	//travar nome vazio

	if (!isset($_POST['name']) || $_POST['name'] == "") {

		User::setErrorRegister("Preencha seu nome.");
		header("Location: /login");
		exit;

	}

	//travar email vazio

	if (!isset($_POST['email']) || $_POST['email'] == "") {

		User::setErrorRegister("Preencha seu email.");
		header("Location: /login");
		exit;

	}

	//travar senha vazia

	if (!isset($_POST['password']) || $_POST['password'] == "") {

		User::setErrorRegister("Preencha sua senha.");
		header("Location: /login");
		exit;

	}


	if (User::checkLoginExist($_POST['email'])) {

		User::setErrorRegister("Usuário já existe, favor verificar.");
		header("Location: /login");
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'despassword'=>$_POST['password'],
		'desemail'=>$_POST['email'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /cart');
	exit;

});

?>