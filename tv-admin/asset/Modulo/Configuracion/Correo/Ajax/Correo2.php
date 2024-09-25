<?php
    require_once "../../../../Clases/SendMail.php";

    class Correo2{

        private $correo;

        public function __construct() {
            $this->correo = new SendMail();
        }

        public function main(){
            $correos = array();
            array_push($correos, array("email"=>"ventasweb@macromautopartes.com", "nombre"=>"ventasweb macromautopartes")); 
            $this->correo->Send("Bienvenido Ventas Macrom",$correos,"<h1></h1>");
        }
    }

    $app = new Correo2();
    $app->main();