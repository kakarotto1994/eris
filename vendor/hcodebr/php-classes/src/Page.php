<?php

namespace Hcode;

use Rain\Tpl;

class Page {

    private $tpl;
    private $options = [];
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data"=>[]
    ];

    public function __construct($opts = array(), $tpl_dir = "/views/"){

        $this->options = array_merge($this->defaults, $opts);

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
           );

        Tpl::configure( $config );

        // create the Tpl object
        $this->tpl = new Tpl;
        
        $this->setData($this->options["data"]);

        // Chama o arquivo que vai se repetir em todas as paginas

        if($this->tpl->draw("header")===true) $this->tpl->draw("header");

    }

    public function setTpl($name, $data = array(), $returnHtml = False){
        $this->setData($data);
        return $this->tpl->draw($name, $returnHtml);
    }

    private function setData($data = array()){

        foreach($data as $key => $value){
            $this->tpl->assign($key, $value);
        }

    } 

    //Cria o rodape, chamando o arquivo
    public function __destruct(){

        if($this->tpl->draw("footer")===true) $this->tpl->draw("footer");

    }

}

?> 