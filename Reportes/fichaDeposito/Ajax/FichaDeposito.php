<?php
    session_name("loginCliente");
    session_start();
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

    class fichaDeposito{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario;

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            $this->jsonData["Bandera"] = 1;    
            $this->jsonData["Data"] = $this->getFichaDeposito($this->formulario->ficha->id);
            print json_encode($this->jsonData);
        }

        private function getFichaDeposito($id){
            $sql = "Select (Importe + cenvio - descuento) as Total, FormaPago from Pedidos where _idPedidos = $id";
            return $this->conn->fetch($this->conn->query($sql));
        }

    }

    $app = new fichaDeposito($array_principal);
    $app->main();
