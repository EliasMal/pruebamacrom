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
                    $this->DeleteCarrito();
                    $this->getallPedidos();
                    $_SESSION["padlock"] = "lock";
                break;
            }
            print json_encode($this->jsonData);
        }
        
        private function actusr(){
            $sql = "UPDATE Pedidos set Acreditado = 1 WHERE _idPedidos =".$_SESSION["id_pedido"];
            return $this->conn->query($sql);
        }

        private function getallPedidos(){
            $sql = "UPDATE Pedidos set Acreditado = 1 WHERE _idPedidos =".$_SESSION["id_pedido"];
            return $this->conn->query($sql);
        }
    
        private function DeleteCarrito(){
            //Envio de registro satisfactorio al Correo del usuario.
            $destinatario ="web.tsuruvolks@gmail.com, webmaster@macromautopartes.com";
            $asunto='Compra en Macromautopartes';
            $mensaje= "Nueva compra registrada en la pagina Macromautopartes, revisar el pedido para su envio.(Metodopago: Tarjeta cred/deb)";
            $email = "webmaster@macromautopartes.com";
            $header ="Enviado desde Macromautopartes";
            $mensajeCompleto = $mensaje."\nAtentamente: Macromautopartes";
            mail($destinatario, $asunto, $mensajeCompleto, $header);
            //Fin Envio de registro satisfactorio al Correo del usuario.
            $sql = "DELETE FROM Carrito WHERE _clienteid = ".$_SESSION["iduser"];
            return $this->conn->query($sql);
        }
    }
    $app = new Model($array_principal);
    $app->main();