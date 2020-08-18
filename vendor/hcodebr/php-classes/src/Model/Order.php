<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Cart;
use \Hcode\Model;

class Order extends Model {

    public function save() {

        $sql = new Sql();

        $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, 
        :idaddress, :vltotal)", [

            ":idorder"=> $this->getidorder(),
            ":idcart"=> $this->getidcart(),
            ":iduser"=> $this->getiduser(),
            ":idstatus"=> $this->getidstatus(),
            ":idaddress"=> $this->getidaddress(),
            ":vltotal"=> $this->getvltotal()

        ]);

        if(count($results[0]) > 0) {

            $this->setData($results[0]);

        }

    }

    public function get($idorder) {

        $sql = new Sql();

        $results = $sql->select("
        select * from 
        tb_orders o 
            left join tb_ordersstatus os
                on o.idstatus = os.idstatus
            join tb_carts c 
                on o.idcart = c.idcart
            left join tb_users u
                on o.iduser = u.iduser
            left join tb_addresses a
                on o.idaddress = a.idaddress
            left join tb_persons p
                on u.idperson = p.idperson
                where o.idorder = :idorder
            ", [
            ":idorder"=>$idorder
        ]);

        if(count($results[0]) > 0) {

            $this->setData($results[0]);

        }


    }

}




?>