<?php
    session_name("loginCliente");
    session_start();
    require_once "../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../tv-admin/asset/Clases/ConexionMySQL.php";
    
    class Model{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();

        function __construct($array=array()){
            $this->conn = new HelperMySql($array["server"],$array["user"],$array["pass"],$array["db"]);
        }
        
        function __destruct() {
            unset($this->conn);
        }
    
        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            switch($this->formulario->modelo->opc){
                case 'efectivo':
                    $this->jsonData["Bandera"] = 1;
                    $_SESSION["padlock"] = "lock";
                break;
                case 'tarjeta':
                    $this->jsonData["Bandera"] = 1;
                    $_SESSION["padlock"] = "lock";
                break;
            }
            print json_encode($this->jsonData);
        }
    }
    $app = new Model($array_principal);
    $app->main();