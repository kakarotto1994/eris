<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model {

        // Lista todos os resultados do banco
    public static function listAll(){

        $sql = new Sql();
        return $sql->select("select * from tb_products order by desproduct");

    }

    public static function checkList($list){

        foreach( $list as &$row ){

            $f = new Product();
            $f->setData($row);
            $row = $f->getvalues();

        }

        return $list;

    }
    

    public function save(){

        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($results[0]);

    }

    public function get($idproduct){

        $sql = new Sql();

        $results = $sql->select("select * from tb_products where idproduct = :idproduct", array(
            ":idproduct"=>$idproduct
        ));

        $this->setData($results[0]);

    }

    //deletar produto
    public function delete(){

        $sql = new Sql();

        $sql->query("delete from tb_products where idproduct = :idproduct", array(
            ":idproduct"=>$this->getidproduct()
        ));

    }

    public function checkPhoto(){

        if(file_exists($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg")){

            $url = "/res/site/img/products/".$this->getidproduct().".jpg";

        } else {

            $url = "/res/site/img/product.jpg";

        }

        return $this->setdesphoto($url);

    }

    public function getValues(){

        $this->checkPhoto();

        $values = parent::getValues();

        return $values;

    }

    public function setPhoto($file){

        $extension = explode('.', $file["name"]);
        $extension = end($extension);

        switch ($extension){
        
            case "jpg";
            case "JPG";
            case "jpeg";
            case "JPEG";
            $image = imagecreatefromjpeg($file["tmp_name"]);
        break;

            case "gif";
            case "GIF";
            $image = imagecreatefromgif($file["tmp_name"]);
        break;

            case "png";
            case "PNG";
            $image = imagecreatefrompng($file["tmp_name"]);
        break;

        }

        $dist = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";

        imagejpeg($image, $dist);

        imagedestroy($image);

        $this->checkPhoto();

    }

    public function getFromUrl($desurl){

        $sql = new Sql();
        
        $rows = $sql->select("
        select * from tb_products where desurl = :desurl
        limit 1
        ", [
            ":desurl"=>$desurl
        ]);

        $this->setData($rows[0]);

    }

    public function getCategories() {

        $sql = new Sql();

        return $sql->select("
            select * from tb_categories c
                left join tb_productscategories cp on c.idcategory = cp.idcategory
                where cp.idproduct = :idproduct
        ", [
            ":idproduct"=>$this->getidproduct()
        ]);

    }


}




?>