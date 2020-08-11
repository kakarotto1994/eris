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

}


?>