<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

    const SESSION = "User";

    public static function login($login, $password)
    {
        $sql = new Sql();

        $results = $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0)
        {
            throw new \Exception("Usuario não encontrado ou senha invalida");
        }

        $data = $results[0];
        
        if(password_verify($password, $data["despassword"])){
            
            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        
        } else {
            throw new \Exception("Usuario não encontrado ou senha invalida");
        }
    }

    // Verifica se o usuario está logado
    public static function verifyLogin($inadmin = true){

        if(
            !isset($_SESSION[User::SESSION]) 
            || 
            !$_SESSION[User::SESSION] 
            || 
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 
            || 
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin) 
        {

            header("Location: /eris/login");
            exit;

        }

    }

    //desloga o usuario
    public static function logout(){

        $_SESSION[User::SESSION] = NULL;

    }

        // Lista todos os resultados do banco
    public static function listAll(){

        $sql = new Sql();
        return $sql->select("select * from tb_users tu left join tb_persons tp on tu.idperson = tp.idperson order by tp.desperson");

    }

    //cadastrar usario
    public function save(){
        $sql = new Sql();
        // pdesperson VARCHAR(64), 
        // pdeslogin VARCHAR(64), 
        // pdespassword VARCHAR(256), 
        // pdesemail VARCHAR(128), 
        // pnrphone BIGINT, 
        // pinadmin TINYINT

        $results = $sql->select("call sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    public function get($iduser){
        $sql = new Sql();
        $results = $sql->select("select * from tb_users tu left join tb_persons tp on tu.idperson = tp.idperson where tu.iduser = :iduser", array (
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    public function update(){
    
        $sql = new Sql();
//  piduser INT,
//  pdesperson VARCHAR(64), 
//  pdeslogin VARCHAR(64), 
//  pdespassword VARCHAR(256), 
//  pdesemail VARCHAR(128), 
//  pnrphone BIGINT, 
//   pinadmin TINYINT

    $results = $sql->select("call sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
        ":iduser"=>$this->getiduser(),
        ":desperson"=>$this->getdesperson(),
        ":deslogin"=>$this->getdeslogin(),
        ":despassword"=>$this->getdespassword(),
        ":desemail"=>$this->getdesemail(),
        ":nrphone"=>$this->getnrphone(),
        ":inadmin"=>$this->getinadmin()
    ));
    $this->setData($results[0]);    
    }

    public function delete(){

        $sql = new Sql();

        $sql->select("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()     
        ));

    }

}




?>