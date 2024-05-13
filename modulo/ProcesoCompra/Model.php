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

            //Envio de compra satisfactoria al Correo del usuario.
            $destinatario = $_SESSION["usr"];
            $nombre = $_SESSION["nombrecorto"];
            $asunto='Compra Macromautopartes';
            $mensaje= '<!DOCTYPE html>
            <html lang="es">
            <head>
            </head>
                <body>
                    <style>
                    .contmenubus, footer, .copyseccion, #myBtn, .carritoshop, .menudesinsreg, .header2, .ft0{display: none;visibility: collapse;height:-100%;width:-100%;}
                    .dpitm{display: flex;justify-content: space-evenly;}
                    .pofam{font-family: Poppins;}
                    </style>
                        <div>
                            <section style="padding-bottom:60px;>
                                <div class="container1" style="width:1000px;">
                                    <div class="row">
                                        <div class="col-md-6 insmob" style="padding-bottom:30px;">
                                            <form name="frmReg" id="frmReg"  novalidate>
                                                <h4><img src="https://macromautopartes.com/images/icons/CRcabecera.png" style="width:100%;"></h4>
                                                    <div style="color:#de0007;text-align:center;">
                                                        <h4 class="pofam" style="font-size:25px;line-height:32px;margin-bottom:0px;">Compra realizada</h4>
                                                        <h4 class="pofam" style="font-size:25px;margin-top:0px">exitosamente.</h4>
                                                    </div>
                                                    <h4 style="text-align:center;"><img src="https://macromautopartes.com/images/icons/CR-caja.png" style="height: 250px;"></h4>
                                                    <div>
                                                        <h4 class="pofam" style="color:#757575;text-align:center;font-size:22px;margin-bottom:0px;">¡Gracias por tu preferencia '.$nombre.'!</h4>
                                                        <h4 class="pofam" style="color:#9e9e9e;text-align:center;font-size:20px;margin-top:0px;">Tu pedido esta siendo revisado, para salir hacia tu destino.</h4>
                                                    </div>
                                                <h4 class="m-text26 prueba3" style="padding-bottom:42px;"><img src="https://macromautopartes.com/images/icons/CRPie-pagina.png" style="width:100%;"></h4>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                </body>
            </html>';
            $email = "webmaster@macromautopartes.com";
            $headers ="MIME.Version: 1.0". "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8". "\r\n";
            mail($destinatario, $asunto, $mensaje, $headers);
            //Fin Envio de compra satisfactoria al Correo del usuario.
            
            $sql = "DELETE FROM Carrito WHERE _clienteid = ".$_SESSION["iduser"];
            return $this->conn->query($sql);
        }
    }
    $app = new Model($array_principal);
    $app->main();