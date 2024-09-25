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
                    //$this->getallPedidos();
                    //$this->setLogcontrol();
                    $_SESSION["padlock"] = "lock";
                break;
            }
            print json_encode($this->jsonData);
        }

        private function getallPedidos(){
            $sql = "UPDATE Pedidos set Acreditado = 1 WHERE _idPedidos =".$_SESSION["id_pedido"];
            return $this->conn->query($sql);
        }
        
        private function setLogcontrol(){
            $sql = "INSERT INTO LogTerminal(_idPedidos, Reference, Responce, folioCpagos, auth, cd_response, fecha, nb_company, cc_type, cc_number, cc_mask) values "
            ."('{$_SESSION["id_pedido"]}','{$_SESSION["datacc"]["referencia"]}','{$_SESSION["datacc"]["nbResponse"]}','{$_SESSION["datacc"]["operacion"]}','{$_SESSION["datacc"]["nuAut"]}','{$_SESSION["datacc"]["cdEmpresa"]}',"
            ."'{$_SESSION["datacc"]["fecha"]}','{$_SESSION["datacc"]["empresa"]}','{$_SESSION["datacc"]["tpTdc"]}','{$_SESSION["datacc"]["sucursal"]}','{$_SESSION["datacc"]["nb_merchant"]}')";
            return $this->conn->query($sql);
        }

        private function DeleteCarrito(){
            //Envio de registro satisfactorio al Correo del usuario.
            $destinatario ="ventasweb@macromautopartes.com";
            $asunto='Compra en Macromautopartes';
            $mensaje= "Nueva compra registrada en la pagina Macromautopartes, revisar el pedido para su envio.(Metodopago: Tarjeta cred/deb)";
            $email = "ventasweb@macromautopartes.com";
            $header .="From: ".$email;
            $mensajeCompleto = $mensaje."\nAtentamente: Macromautopartes";
            mail($destinatario, $asunto, $mensajeCompleto, $header);    
            //Fin Envio de registro satisfactorio al Correo del usuario.

            //Envio de compra satisfactoria al Correo del usuario.
            $destinatario = $_SESSION["usr"];
            $nombre = $_SESSION["nombrecorto"];
            $asunto='Compra Macromautopartes';
            $mensaje= '<!DOCTYPE html>
            <html lang="es">
            <head>
            </head>
                <body>
                    <div style="width:100%;">
                        <section>
                            <div>
                                <img style="width:100%;" src="https://macromautopartes.com/images/icons/CRcabecera.png">
                                    <div style="display: grid;text-align: center;">
                                        <h1 style="color:#de0007;font-size:30px;">¡Compra realizada exitosamente!</h4>
                                        <h4><img style="height:250px" src="https://macromautopartes.com/images/icons/CR-caja.png"></h4>
                                        <h3 style="color:#000;">¡Gracias por tu preferencia '.$nombre.'!<br>Tu pedido esta siendo revisado, para salir hacia tu destino.</h4>
                                    </div>
                                <img style="width:100%;" src="https://macromautopartes.com/images/icons/CRPie-pagina.png">
                            </div>
                        </section>
                    </div>
                </body>
            </html>';
            $email = "ventasweb@macromautopartes.com";
            $headers ="MIME.Version: 1.0". "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8". "\r\n";
            $headers .="From: ".$email;
            mail($destinatario, $asunto, $mensaje, $headers);
            //Fin Envio de compra satisfactoria al Correo del usuario.
            
            $sql = "DELETE FROM Carrito WHERE _clienteid = ".$_SESSION["iduser"];
            return $this->conn->query($sql);
        }
    }
    $app = new Model($array_principal);
    $app->main();