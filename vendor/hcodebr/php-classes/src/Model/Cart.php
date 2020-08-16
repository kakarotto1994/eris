<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\model\Product;
use \Hcode\Model\User;

class Cart extends Model {

    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";

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
        $results = $sql->select("Call sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays()
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

        $this->getCalculateTotal();

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

        $this->getCalculateTotal();

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


    // total dimensoes e peso do carrinho. Soma de todos os atributos do carrinho
    public function getProductsTotals() {

        $sql = new Sql();
        $results = $sql->select("
        select sum(vlprice) as vlprice, sum(p.vlwidth) as vlwidth, 
        sum(p.vlheight) as vlheight, sum(vllength) as vllength, sum(vlweight) vlweight, count(*) as qtd
            from tb_products p 
            join tb_cartsproducts cp on p.idproduct = cp.idproduct
            join tb_carts c on cp.idcart = c.idcart
            where cp.idcart = :idcart
                and cp.dtremoved is null", [
            ":idcart"=>$this->getidcart()
        ]);

        if(count($results) > 0) {

            return $results[0];

        } else {

            return [];

        }

    }


    public function setFreight($nrzipcode) {

        $nrzipcode = str_replace("-", "", $nrzipcode);

        $totals = $this->getProductsTotals();

        if(count($totals["qtd"]) > 0) {

            if($totals["vlheight"] < 1) $totals["vlheight"] = 1;
            if($totals["vllength"] < 15) $totals["vllength"] = 15;


            $qs = http_build_query([
                "nCdEmpresa"=>'',
                "sDsSenha"=>'',
                "nCdServico"=>'40010',
                "sCepOrigem"=>'13219070',
                "sCepDestino"=>$nrzipcode,
                "nVlPeso"=>$totals['vlweight'],
                "nCdFormato"=>'1',
                "nVlComprimento"=>$totals["vllength"],
                "nVlAltura"=>$totals["vlheight"],
                "nVlLargura"=>$totals["vlwidth"],
                "nVlDiametro"=>'0',
                "sCdMaoPropria"=>'S',
                "nVlValorDeclarado"=>$totals['vlprice'],
                "sCdAvisoRecebimento"=>'S'
            ]);

            $ws = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs;

            //executa o xml dos correios
            $xml = simplexml_load_file($ws);

            $result = $xml->Servicos->cServico;

            if ($result->MsgErro != "") {

                Cart::setMsgError($result->MsgErro);

            } else {

//                Cart::clearMsgError();

            }
            
            $this->setnrdays($result->PrazoEntrega + 1);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

           $this->save();

            return $result; 

        } else {



        }


    }

    public static function formatValueToDecimal($value):float {

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return $value;

    }

    public static function setMsgErrror($msg) {

        $_SESSION[Cart::SESSION_ERROR] = $msg;

    } 

    public static function getMsgError() {

        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;

    }

    public static function clearMsgError() {

        $_SESSION[Cart::SESSION_ERROR] = NULL;

    }


    public function updateFreight() {

        if($this->getdeszipcode() != "" || $this->getdeszipcode() > 0) {

            $this->setFreight($this->getdeszipcode());

        }

    }

    public function getValues() {

        $this->getCalculateTotal();

        return parent::getValues();

    }

    public function getCalculateTotal() {

        $this->updateFreight();

        $totals = $this->getProductsTotals();

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());


    }


}




?>