<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

//rota index php
$app->get('/', function() {

	$products = Product::listAll();

	$cart = new Cart();

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

	$cart = Cart::getFromSession();

	$totals = $cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::STATUS_EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder());
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


$app->get('/order/:idorder', function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()

	]);

});

//gerar boleto

$app->get('/boleto/:idorder', function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 5;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress().', '.$order->getdescomplement()." - ".$order->getdesdistrict();
	$dadosboleto["endereco2"] = "Cidade: ".$order->getdescity()." - Estado: ".$order->getdesstate()." -  CEP: ".$order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Eris Mega Store";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 5 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: eris.pomo@gmail.com";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Eris Mega Store - www.eeris.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "7431"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "12869";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "8"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Eris Mega Store";
	$dadosboleto["cpf_cnpj"] = "32.631.091/0001-50";
	$dadosboleto["endereco"] = "Avenida bento Amaral Gurgel, 13219-070";
	$dadosboleto["cidade_uf"] = "Jundiai - SP";
	$dadosboleto["cedente"] = "Eris MegaStore - ME";

	$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."boletophp".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;

	// NÃO ALTERAR!
	require_once($path."funcoes_itau.php");
	require_once($path."layout_itau.php");

	// include("include/funcoes_itau.php"); 
	// include("include/layout_itau.php");

});

// Meus pedidos

$app->get("/profile/orders", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders() 
	]);

});

$app->get("/profile/orders/:idorder", function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getOrderProducts()
	]);

});


?>