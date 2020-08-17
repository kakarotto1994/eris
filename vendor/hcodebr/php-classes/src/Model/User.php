<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

    const SESSION = "User";
    const ENCRYPT = "Eris_pomo_secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";

    public static function getFromSession(){

        $user = new User();

        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION] > 0){

            $user->setData($_SESSION[User::SESSION]);

        }

        return $user;

    }

    public static function checkLogin($inadmin = true) {

        if(!isset($_SESSION[User::SESSION]) 
        || 
        !$_SESSION[User::SESSION] 
        || 
        !(int)$_SESSION[User::SESSION]["iduser"] > 0) {

            //Não está Logado.
            return false;

        } else {

            if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {

                return true;

            } else if ($inadmin === false) {

                return true;

            } else {

                return false;

            }

        }

    }

    public static function login($login, $password)
    {
        $sql = new Sql();

        $results = $sql->select("select * from tb_users u join tb_persons p on u.idperson = p.idperson where deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0)
        {
            throw new \Exception("Usuario não encontrado ou senha invalida");
        }

        $data = $results[0];
        
        if(password_verify($password, $data["despassword"])){
            
            $user = new User();

            $data['desperson'] = utf8_encode($data['desperson']);

            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        
        } else {
            throw new \Exception("Usuario não encontrado ou senha invalida");
        }
    }

    // Verifica se o usuario está logado
    public static function verifyLogin($inadmin = true){

        if(!User::checkLogin($inadmin)) 
        {
            if($inadmin) {
                header("Location: /eris/login");

            } else {
                header("Location: /login");
            }

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
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
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

        $data = $results[0];

        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);

        
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
        ":desperson"=>utf8_decode($this->getdesperson()),
        ":deslogin"=>$this->getdeslogin(),
        ":despassword"=>User::getPasswordHash($this->getdespassword()),
        ":desemail"=>$this->getdesemail(),
        ":nrphone"=>$this->getnrphone(),
        ":inadmin"=>$this->getinadmin()
    ));
    $this->setData($results[0]);    
    }

    public function delete(){

        $sql = new Sql();


        $call = "CALL sp_users_delete(:iduser)";

        $sql->select($call, array(
            ":iduser"=>$this->getiduser()     
        ));

    }

    public static function getForgot($email, $inadmin = true) {
        $sql = new Sql();
        $results = $sql->select(
            "select * from tb_persons tp
                join tb_users tu on tp.idperson = tu.idperson
            where tp.desemail =  :EMAIL", 
        array(
            ":EMAIL"=>$email
        ));
        
        if(count($results) === 0){
            throw new \Exception("Email não encontrado. Favor valide as informações ou contacte um ADM");
        } else
        {

            $data = $results[0];

            $forgot = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(

                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]

            ));

            if(count($forgot)===0){

                throw new \Exception("Email não encontrado. Favor valide as informações ou contacte um ADM");
            } else {

                $dataRecovery = $forgot[0];
                            
                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,User::ENCRYPT,$dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

                if($inadmin) {

                    $link = "http://www.eeris.com.br/eris/forgot/reset?code=$code";

                } else {

                    $link = "http://www.eeris.com.br/forgot/reset?code=$code";

                }

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinicao Senha Eris MegaStore", "forgot", array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                ));
                
                $mailer->send();

                return $data;
            }

        }

    }

    public static function validForgotDecrypt($code){

        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,User::ENCRYPT, base64_decode($code), MCRYPT_MODE_ECB);

        $sql = new Sql();


        $results = $sql->select(
            "SELECT * 
            FROM db_ecommerce.tb_userspasswordsrecoveries rec
                left join tb_users tu on rec.iduser = tu.iduser
                left join tb_persons tp on tp.idperson = tu.idperson
            where rec.idrecovery = :idrecovery
                and rec.dtrecovery is null
                and now() <= date_add(rec.dtregister, interval 70 minute);", 
            array(
                ":idrecovery"=>$idrecovery
            ));

            
        if(count($results)===0){
            throw new \Exception("Não foi possivel recuperar a senha");
        } else {

            return $results[0];

        }
    }

    public static function setForgotUsed($idrecovery){

        $sql = new Sql();

        $sql->query("update tb_userspasswordsrecoveries set dtrecovery = now() where idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));

    }

    public function setPassword($password){
        $sql = new Sql();

        $sql->query("update tb_users set despassword = :despassword where iduser = :iduser", array (
            ":despassword"=>$password,
            ":iduser"=>$this->getiduser()
        ));

    }

    public static function setError($msg) {

        $_SESSION[User::ERROR]  = $msg;

    }

    public static function getError() {

        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
        
        User::clearError();

        return $msg;

    }

    public static function clearError() {

        $_SESSION[User::ERROR] = NULL;

    } 

    public static function getPasswordHash($password) {

        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>10
        ]);

    }

    public static function setErrorRegister($msg) {

        $_SESSION[User::ERROR_REGISTER] = $msg;

    }

    public static function getErrorRegister(){

        // User::clearErrorRegister();


        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";

        return $msg;

    }

    public static function clearErrorRegister() {

        $_SESSION[User::ERROR_REGISTER] = NULL;

    }

    //Valida se o usuario já existe

    public static function checkLoginExist($login) {

        $sql = new Sql();

        $results = $sql->select("
        select * from tb_users where deslogin = :deslogin
        ", [
            ":deslogin"=>$login
        ]);

        return (count($results) > 0);



    }

}

?>