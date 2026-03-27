<?php
    session_name("loginCliente");
    session_start();
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

    class fichaDeposito{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            if(!isset($_SESSION["id_pedido"]) || empty($_SESSION["id_pedido"])){
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["mensaje"] = "No se encontró ningún pedido activo.";
                print json_encode($this->jsonData);
                return;
            }

            $id_pedido = intval($_SESSION["id_pedido"]);
            $this->jsonData["Bandera"] = 1;    
            $this->jsonData["Data"] = $this->getFichaDeposito($id_pedido);
            $this->jsonData["Data"]["folio_real"] = $id_pedido; 
            
            print json_encode($this->jsonData);
        }

        private function getFichaDeposito($id){
            $sql = "Select (Importe + cenvio - descuento) as Total, FormaPago, noPedido from Pedidos where _idPedidos = $id LIMIT 1";
            $this->conn->query($sql);
            return $this->conn->fetch();
        }
    }

    $app = new fichaDeposito($array_principal);
    $app->main();
?>