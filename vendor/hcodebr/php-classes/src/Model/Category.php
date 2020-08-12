<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

        // Lista todos os resultados do banco
    public static function listAll(){

        $sql = new Sql();
        return $sql->select("select * from tb_categories order by descategory");

    }

    public function save(){

        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]);

    }

    public function get($idcategory){

        $sql = new Sql();

        $results = $sql->select("select * from tb_categories where idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
        ));

        $this->setData($results[0]);

    }

    //deletar categoria
    public function delete(){

        $sql = new Sql();

        $sql->query("delete from tb_categories where idcategory = :idcategory", array(
            ":idcategory"=>$this->getidcategory()
        ));

    }

}




?>