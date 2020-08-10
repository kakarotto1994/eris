<?php

namespace Hcode;

use Rain\Tpl;

class Page {

    private $tpl;
    private $options = [];
    private $defaults = [
        "data"=>[]
    ];

    public function __construct($opts = array()){

        $this->options = array_merge($this->defaults, $opts);

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
           );

        Tpl::configure( $config );

        // create the Tpl object
        $this->tpl = new Tpl;
        
        $this->setData($this->options["data"]);

        // Chama o arquivo que vai se repetir em todas as paginas

        $this->tpl->draw("header");

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

        $this->tpl->draw("footer");

    }

}

?> 