<?php

    namespace Hcode;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    use Rain\Tpl;

    class Mailer {

        CONST USERNAME = "eris.pomo@gmail.com";
        Const PASSWORD = "eris.1234";
        const NAME_FROM = "Eris MegaStore";

        private $mail;

        public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
        {

            $config = array(
                "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/",
                "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
                "debug"         => false // set to false to improve the speed
               );
    
            Tpl::configure( $config );
    
            // create the Tpl object
            $this->tpl = new Tpl; 

            foreach ($data as $key -> $value){
                $tpl->assign($key, $value);
            }

            $html = $tpl->draw($tplName, true);

            $this->mail = new PHPMailer(true);

            $this->mail->isSMTP();

            $this->mail->SMTPDebug = 0;

            $this->mail->Debugoutput = "html";

            $this->mail->Host = 'smtp.gmail.com';

            $this->mail->Port = 465;

            $this->mail->SMTPSecure = 'ssl';

            $this->mail->SMTPAuth = true;

            $this->mail->username = "Mailer::USERNAME";

            // eris.1234

            $this->mail->Password = "Mailer::PASSWORD";

            $this->mail->setFrom(Mailer::USERNAME ,Mailer::NAME_FROM);

            $this->mail->addAddress($toAddrss, $toName);

            $this->mail->Subject = $subject;

            //file_get_contents('contents.html'), dirname(__FILE__)
            $this->mail->msgHTML($html);

            $this->mail->aLTbODY = "This is a plain-text message body";

        }

        public function send(){
            if ($this->mail->send()){
                echo "true";
            }else{
                echo "false";
            }  
            return $this->mail->send();
        } 

    }

?>