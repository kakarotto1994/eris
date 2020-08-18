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

	$address = new Address();

	$cart = Cart::getFromSession();

	
	if(!isset($_GET['zipcode']) || isset($_GET['zipcode']) == '' || isset($_GET['zipcode']) == 0) {

		$_GET['zipcode'] = $cart->getdeszipcode();

	}

	if(isset($_GET['zipcode'])) {

		$address->loadFromCep($_GET['zipcode']);
		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();
		$cart->getCalculateTotal();
		

	}


	if(!$address->getdesaddress()) $address->setdesaddress('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdeszipcode()) $address->setdeszipcode('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdescomplement()) $address->setdescomplement('');
	

	$page = new Page();

	$page->setTpl('checkout', [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()

	]);


});

$app->post("/checkout", function() {

	User::verifyLogin(false);

	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {

		Address::setMsgError("Cep invalido ou não informado");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {

		Address::setMsgError("Endereço invalido ou não informado");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {

		Address::setMsgError("Bairro invalido ou não informado");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['descity']) || $_POST['descity'] === '') {

		Address::setMsgError("Cidade invalido ou não informado");
		header("Location: /checkout");
		exit;
	}

	if(!isset($_POST['desstate']) || $_POST['desstate'] === '') {

		Address::setMsgError("Estado invalido ou não informado");
		header("Location: /checkout");
		exit;
	}


	$user = User::getFromSession();

	$address =new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit;

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

//Esqueci a senha

$app->get("/forgot", function() {
	$page = new Page();

	$page->setTpl("forgot");
});

$app->post("/forgot", function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

$app->get("/forgot/sent", function() {

	$page = new Page();

	$page->setTpl("forgot-sent");

});

$app->get("/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/forgot/reset", function() {



	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>10
	]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});


//Minha conta Profile

$app->get("/profile", function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [

		"user"=>$user->getValues(),
		"profileMsg"=>User::getSuccess(),
		"profileError"=>User::getError()

	]);

	User::clearSuccess();
	User::clearError();


});

$app->post("/profile", function() {

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] == "") {

		User::setError("Preencha seu nome.");
		header('Location: /profile');
		exit;

	}

	//travar email vazio

	if (!isset($_POST['desemail']) || $_POST['desemail'] == "") {

		User::setError("Preencha seu email.");
		header('Location: /profile');
		exit;

	}

	$user = User::getFromSession();

	//validar troca email

	if($_POST['desemail'] !== $user->getdesemail()) {

		if(User::checkLoginExist($_POST['desemail'])){

			User::setError("Usuário/email já cadastrado, favor verificar.");
			header('Location: /profile');
			exit;

		}

	}

	$_POST['inadmin'] = $user->getinadmin();
	
	if(isset($_POST['despassword'])) {

		if(passowrd_verify($_POST['despassword'], $user->getdespassword())){
		
			$_POST['despassword'] = $user->getdespassword();
		
		} else {

			User::setError("Erro ao acessar o metodo. Favor realizar o reset da senha pelos metodos corretos.");
			header('Location: /profile');
			exit;

		}

	} 
	

	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	$user = User::getFromSession();

	User::setSuccess("Alterações realizadas com sucesso :) ");

	header("Location: /profile");
	exit;

});


?>