<?php
    require_once "../../../../Clases/SendMail.php";

    class Correo2{

        private $correo;

        public function __construct() {
            $this->correo = new SendMail();
        }

        public function main(){
            $correos = array();
            array_push($correos, array("email"=>"webmaster@macromautopartes.com", "nombre"=>"webmaster macromautopartes")); 
            $this->correo->Send("Bienvenido Francisco Ivan",$correos,"<h1></h1>");
        }
    }

    $app = new Correo2();
    $app->main();