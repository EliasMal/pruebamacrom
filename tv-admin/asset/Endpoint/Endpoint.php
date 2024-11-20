<?php
include("../Clases/ConexionMySQL.php");
include("../Clases/AESCrypto.php");
include("../Clases/dbconectar.php");
include('../../../vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;


Class Endpoint{
    //private $key = "5dcc67393750523cd165f17e1efadd21"; //semilla para pruebas
    private $key = "DA6DAC61810C99C5017DE457560134C6";
    private $AES;
    private $respuesta;
    private $xml;
    private $datauser = array();


    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->AES = new AESCrypto();
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main (){
        if($_POST){
    
            /* Genero un log para saber si la recepcion es correcta del Endpoint */
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "--------------------------\n");
            fwrite($filelog, $_REQUEST["strResponse"]);
            fwrite($filelog, "Respuesta en claro--------\n");
            $this->respuesta = $this->AES->desencriptar($_REQUEST["strResponse"],$this->key);
            $this->xml = simplexml_load_string($this->respuesta);
            fwrite($filelog, $this->respuesta);
            fwrite($filelog, "--------------------------\n");
            fwrite($filelog, $this->xml->response);
            //$this->xml->reference = "29"; // ese valor solo sera para el desarrollo se tiene que eliminar cuando este en produccion
            $id = $this->getPedidoId();
            $userid = $this->getPedidoUserId();
            $datauser = $this->getUser($userid);
            if($this->xml->response == "approved"){
                fwrite($filelog, "Obtengo el id del pedido-----------------\n");
                fwrite($filelog, $id);
                $this->setPedido( $id, 1);
                $fechacompleta = $this->xml->date." ".$this->xml->time;
                $this->DeleteCarrito($datauser,$fechacompleta);
            }else{
                $this->setPedido($id,6);
            }
            fwrite($filelog, $this->setLogTerminal($id) . "Obtengo el id del pedido-----------------\n");
            fclose($filelog);
 
        }else{
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "no se recibio ningun post");
            fclose($filelog);
        }
    } 
    private function getUser($userid){
        $sql = "SELECT * FROM clientes WHERE _id = $userid";
        $result = $this->conn->fetch($this->conn->query($sql));
        return $result;
    }
    private function getPedidoUserId(){
        $sql = "Select _idCliente from Pedidos where noPedido = {$this->xml->reference}";
        $res = $this->conn->fetch($this->conn->query($sql));
        return $res["_idCliente"];
    }
    private function getPedidoId(){
        $sql = "Select _idPedidos from Pedidos where noPedido = {$this->xml->reference}";
        $res = $this->conn->fetch($this->conn->query($sql));
        return $res["_idPedidos"];
    }

    private function setPedido($id, $acreditado){
        $sql = "UPDATE Pedidos SET Acreditado = $acreditado where _idPedidos = $id";
        return $this->conn->query($sql)? true:false;
    }
    
    private function setLogTerminal($id){
        $array = explode("/",$this->xml->date);
        $fecha = $this->xml->date != ""? "{$array[2]}-{$array[1]}-{$array[0]}":date("Y-m-d");
        $sql = "INSERT INTO LogTerminal(_idPedidos, Reference, Responce, folioCpagos, auth, cd_response, fecha, nb_company, cc_type, cc_number, cc_mask)
        value($id, '{$this->xml->reference}','{$this->xml->response}','{$this->xml->foliocpagos}','{$this->xml->auth}','{$this->xml->cd_response}',
        '{$fecha}','{$this->xml->nb_company}','{$this->xml->cc_type}','{$this->xml->cc_number}','{$this->xml->cc_mask}')";
        return $this->conn->query($sql)? "Datos Ingresados":"Error en la insercion $sql";
    }

    private function DeleteCarrito($user,$fechacompleta){
        //Envio de registro satisfactorio al Correo del usuario.
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'ventasweb@macromautopartes.com';
        $mail->Password = 'jSJLK6AqN%fwUOskf5@R';
        $mail->setFrom('ventasweb@macromautopartes.com', 'Ventas Macrom');
        $mail->addAddress('ventasweb@macromautopartes.com', 'Ventas');
        $mail->Subject = 'Compra en Macromautopartes';
        $mail->IsHTML(true);
        $mail->CharSet = 'utf-8';
        $mail->Body ='Nueva compra registrada en la pagina Macromautopartes, revisar el pedido para su envio. (Metodopago: Deposito/transferencia)';
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
        //Fin Envio de registro satisfactorio al Correo del usuario.

        //Envio de compra satisfactoria al Correo del usuario.
        $destinatario = $user["correo"];
        $nombre = $user["nombres"];
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'ventasweb@macromautopartes.com';
        $mail->Password = 'jSJLK6AqN%fwUOskf5@R';
        $mail->setFrom('ventasweb@macromautopartes.com', 'Ventas Macrom');
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = 'Compra Macromautopartes';
        $mail->IsHTML(true);
        $mail->CharSet = 'utf-8';
        $mail->Body ='
        <html lang="es">
            <body>
                <div style="width:100%;">
                    <section>
                        <div>
                            <img style="width:100%;" src="https://macromautopartes.com/images/icons/CRcabecera.png">
                            <div style="display: grid;text-align: center;">
                                <h1 style="color:#de0007;font-size:30px;">¡Compra realizada exitosamente!</h1>
                                <h4><img style="height:250px" src="https://macromautopartes.com/images/icons/CR-caja.png"></h4>
                                <h3 style="color:#000;">¡Gracias por tu preferencia '.$nombre.'!<br>Tu pedido esta siendo revisado, para salir hacia tu destino.</h3>
                            </div>
                            <p style="text-align:right;margin:0;">Fecha Compra:'.$fechacompleta.'</p>
                            <img style="width:100%;" src="https://macromautopartes.com/images/icons/CRPie-pagina.png">
                        </div>
                    </section>
                </div>
            </body>
        </html>';
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
        //Fin Envio de compra satisfactoria al Correo del usuario.
        
        $sql = "DELETE FROM Carrito WHERE _clienteid = ".$user["_id"];
        return $this->conn->query($sql);
    }

}

$app = new Endpoint($array_principal);
$app->main();