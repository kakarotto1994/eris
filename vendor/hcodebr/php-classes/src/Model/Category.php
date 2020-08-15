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

        Category::updateFile();

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

        Category::updateFile();

    }

    //atualizar menu categorias
    public static function updateFile(){

        $categories = Category::listAll();

        $html = [];

        foreach($categories as $row){

            array_push(
                $html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>'
            );

        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));

    }


    public function getProducts($related = true) {

        $sql = new Sql();

        if($related === true) {

            return $sql->select(
                "select * from tb_products where idproduct in (
                    select p.idproduct from tb_products p 
                        left join tb_productscategories pc on p.idproduct = pc.idproduct
                    where pc.idcategory = :idcategory
                ); ", [
                    ":idcategory"=>$this->getidcategory()
                ]);

        } else {

            return $sql->select("
            select * from tb_products where idproduct not in (
                select p.idproduct from tb_products p 
                    left join tb_productscategories pc on p.idproduct = pc.idproduct
                where pc.idcategory = :idcategory
                );
            ", [
                ":idcategory"=>$this->getidcategory()
            ]);

        }

    }


    public function addProduct (Product $product) {

        $sql = new Sql();

        $sql->query("insert into tb_productscategories (idcategory, idproduct) values (:idcategory, :idproduct)", [
            ":idcategory"=>$this->getidcategory(),
            ":idproduct"=>$product->getidproduct()
        ]);

    }

    public function removeProduct (Product $product) {

        $sql = new Sql();

        $sql->query("delete from tb_productscategories where idcategory = :idcategory and idproduct = :idproduct", [
            ":idcategory"=>$this->getidcategory(),
            ":idproduct"=>$product->getidproduct()
        ]);

    }

    public function getProductsPage($page = 1, $itemsPerPage = 12) {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();
        $results = $sql->select("
            select sql_calc_found_rows * from tb_products p
                join tb_productscategories pc on p.idproduct = pc.idproduct
                join tb_categories c on pc.idcategory = c.idcategory
                where c.idcategory = :idcategory
            limit $start, $itemsPerPage;
        ", [
            "idcategory"=>$this->getidcategory()
        ]);

        $tot = $sql->select("
            select found_rows() as tot;");

            return [
                "data"=>Product::checkList($results),
                "total"=>(int)$tot[0]["tot"],
                "pages"=>ceil($tot[0]["tot"]/$itemsPerPage)];
    }

}




?>