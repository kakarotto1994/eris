<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\model\Product;
use \Hcode\Model\User;

class Cart extends Model {

    const SESSION = "Cart";

        // Lista todos os resultados do banco
    public static function listAll(){

        $sql = new Sql();
        return $sql->select("select * from tb_products order by desproduct");

    }


    public static function getFromSession(){

        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
            
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } else {

            $cart->getFromSessionID();

            if(!(int)$cart->getidcart() > 0) {

                $data = [
                    'dessessionid'=>session_id()
                ];

                if(User::checkLogin(false)){

                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();

                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }

        }

        return $cart;

    }


    public function setToSession () {

        $_SESSION[Cart::SESSION] = $this->getValues();

    } 


    public function getFromSessionID() {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM db_ecommerce.tb_carts where dessessionid = :dessessionid", [
            ":dessessionid"=>session_id()
        ]);
        
        if(count($results) > 0) {
            $this->setData($results[0]);
        }

    }

    public function get(int $idcart) {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM db_ecommerce.tb_carts where idcart = :idcart", [
            ":idcart"=>$idcart
        ]);

        if(count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function save()
    {

        $sql = new Sql();
        $results = $sql->query("Call sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :pnrdays)", [
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":pnrdays"=>$this->getpnrdays()
        ]);

        $this->setData($results[0]);

    }

    //adicionar produtos ao carrinho

    public function addProduct(Product $product) {

        $sql = new Sql();
        $sql->query("insert into tb_cartsproducts (idcart, idproduct, dtregister) values (:idcart, :idproduct, now())", [
            ":idcart"=>$this->getidcart(),
            ":idproduct"=>$product->getidproduct()
        ]);
    }

    //remover produtos do carrinho
    public function removeProduct(Product $product, $all = false) {

        $sql = new Sql();

        if($all) {

            $sql->query("update tb_cartsproducts set dtremoved = now() where idproduct = :idproduct and idcart = :idcart
            and dtremoved is null", [
                ":idcart"=>$this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);

        } else {

            $sql->query("update tb_cartsproducts set dtremoved = now() where idproduct = :idproduct and idcart = :idcart 
            and dtremoved is null limit 1", [
                ":idcart"=>$this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);

        }

    }

    public function getProducts() {

        $sql = new Sql();

        $row = $sql->select("
        select p.idproduct, p.desproduct, p.vlprice, p.vlwidth, p.vlheight, p.vllength, p.vlweight, p.desurl, count(*) as qtd, sum(p.vlprice) as vltotal  
        from tb_cartsproducts cp 
            join tb_products p on cp.idproduct = p.idproduct 
        where cp.idcart = :idcart and cp.dtremoved is null 
            group by p.idproduct, p.desproduct, p.vlprice, p.vlwidth, p.vlheight, p.vllength, p.vlweight, p.desurl
            order by p.desproduct;", [
                ":idcart"=>$this->getidcart()
            ]);

        return Product::checkList($row);

    }



}




?>