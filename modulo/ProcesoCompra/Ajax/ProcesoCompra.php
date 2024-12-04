<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProcesoCompra
 *
 * @author francisco
 */
session_name("loginCliente");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
require_once "../../../tv-admin/asset/Clases/AESCrypto.php";
require_once '../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

date_default_timezone_set('America/Mexico_City');

class ProcesoCompra {
    //put your code here
    private $conn;
    private $AES;
    private $Http;
    private $response;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $cadenaOriginal = "";
    // private $key = "5DCC67393750523CD165F17E1EFADD21"; //semilla para pruebas
    private $key = "DA6DAC61810C99C5017DE457560134C6"; //semilla para produccion
    private $encryptedString = "";
    //Datos para desarrollo
    // private $dataEmpresa = array("id_company"=>"SNBX", "id_branch"=>"01SNBXBRNCH", "user"=>"SNBXUSR0123", "pwd"=>"SECRETO");
    //Datos para produccion
    private $dataEmpresa = array("id_company"=>"JGCA", "id_branch"=>"0001", "user"=>"JGCASIUS0", "pwd"=>"1S6VTAHKL");


    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->AES = new AESCrypto();
        //$this->Http = new HttpRequest();
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        
        switch($this->formulario->Costumer->opc){
            case 'getC':
                $this->jsonData["Data"] = $this->getOneCostumer();
                $this->jsonData["Bandera"] = 1;
            break;

            case 'setC':
                $this->jsonData["Bandera"] = $this->setOneCostumer()? 1:0;
                $this->jsonData["mensaje"] = $this->setOneCostumer()? "Los datos del envío han sido cambiados":"Error al interntar cambiar los datos de envío";
                $this->jsonData["Data"] = $this->formulario->Costumer;
            break;
            case 'buy2':
                $this->formulario->Costumer->noPedido = $this->getnoPedido();
                $this->formulario->Costumer->Importe = ($this->formulario->Costumer->Subtotal + $this->formulario->Costumer->Cenvio->Costo) - $this->formulario->Costumer->descuento;
                if($this->formulario->Costumer->Importe != 0){
                    switch($this->formulario->Costumer->metodoPago){
                        case 'Deposito':
                        case 'Transferencia':
                            $id = $this->setPedido2($this->formulario->Costumer->profile->id, $this->formulario->Costumer->Subtotal, 
                            $this->formulario->Costumer->metodoPago, $this->formulario->Costumer->noPedido["folio"],
                            $this->formulario->Costumer->Cenvio->Costo, $this->formulario->Costumer->Cenvio->Servicio,
                            intval($this->formulario->Costumer->facturacion), isset($this->formulario->Costumer->dataFacturacion->data->_id)? 
                            $this->formulario->Costumer->dataFacturacion->data->_id:0,
                            $this->formulario->Costumer->dataDomicilio->data->_id,0,
                            $this->formulario->Costumer->descuento,
                            $this->formulario->Costumer->Cenvio->paqueteria,
                            $this->formulario->Costumer->Cenvio->enviodias,
                            $this->formulario->Costumer->DiaEstimado,
                            $this->formulario->Costumer->Medidas->height,
                            $this->formulario->Costumer->Medidas->length,
                            $this->formulario->Costumer->Medidas->width,
                            $this->formulario->Costumer->Medidas->weight);
                            if($id){
                                if($this->setPedidosDetalles($id)){
                                    unset($_SESSION["CarritoPrueba"]);
                                    unset($_SESSION["padlock"]);
                                    $this->deleteCarrito();
                                    $_SESSION["id_pedido"] = $id;
                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "Tu pedido se a generado satisfactoriamente";
                                    $this->jsonData["Data"] = $id;

                                    if($this->formulario->Costumer->descuento > 0){
                                        $this->setcuponacre(); 
                                    }            
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
                                    $destinatario = $_SESSION["usr"];
                                    $nombre = $_SESSION["nombrecorto"];
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
                                }else{
                                    $this->jsonData["Bandera"] = 0;
                                    $this->jsonData["Mensaje"] = "Error no se genero el pedido";
                                }
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error al generar tu pedido por favor contactarse con la refaccionaria";
                            }
                            $_SESSION["Cenvio"]["costo"] = 0;
                            $_SESSION["Cenvio"]["Servicio"]= "";
                            //Aqui incrementamos el folio del numero de orden el no de pedido
                            $this->setnoPedido();
                        break;

                        case 'Tarjeta':
                            $this->cadenaOriginal = $this->setXML();
                            $this->encryptedString = $this->AES->encriptar($this->cadenaOriginal, $this->key);
                            $this->response = $this->AES->desencriptar($this->sendPost(),$this->key);
                            $tempgetXml = $this->getXML();
                            if($tempgetXml["res"]){
                                $id = $this->setPedido2($this->formulario->Costumer->profile->id, $this->formulario->Costumer->Subtotal, 
                                $this->formulario->Costumer->metodoPago, $this->formulario->Costumer->noPedido["folio"],
                                $this->formulario->Costumer->Cenvio->Costo, $this->formulario->Costumer->Cenvio->Servicio,
                                intval($this->formulario->Costumer->facturacion), isset($this->formulario->Costumer->dataFacturacion->data->_id)? 
                                $this->formulario->Costumer->dataFacturacion->data->_id:0,
                                $this->formulario->Costumer->dataDomicilio->data->_id,0,
                                $this->formulario->Costumer->descuento,
                                $this->formulario->Costumer->Cenvio->paqueteria,
                                $this->formulario->Costumer->Cenvio->enviodias,
                                $this->formulario->Costumer->DiaEstimado,
                                $this->formulario->Costumer->Medidas->height,
                                $this->formulario->Costumer->Medidas->length,
                                $this->formulario->Costumer->Medidas->width,
                                $this->formulario->Costumer->Medidas->weight);

                                if($this->setPedidosDetalles($id)){
                                    $_SESSION["id_pedido"] = $id;
                                    unset($_SESSION["padlock"]);
                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "";
                                    $this->jsonData["data"] = $tempgetXml["url"];
                                    if($this->formulario->Costumer->descuento > 0){
                                        $this->setcuponacre(); 
                                    }  
                                }else{
                                    $this->jsonData["Bandera"] = 0;
                                    $this->jsonData["Mensaje"] = "Error no se genero el pedido";
                                }
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"]="Error no se genero la liga para el cobro por la tarjeta de credito";
                            }
                            $_SESSION["Cenvio"]["costo"] = 0;
                            $_SESSION["Cenvio"]["Servicio"]= "";
                            //Aqui incrementamos el folio del numero de orden el no de pedido
                            $this->setnoPedido();
                        break;
                    }
                }
                
            break;
        }
        
        print json_encode($this->jsonData);
    }
    
    private function getOneCostumer (){
        $sql = "SELECT * FROM clientes where Estatus = 1 and correo='{$this->formulario->Costumer->usr}'";
        return $this->conn->fetch($this->conn->query($sql));
        
    }
   // UPDATE macromau_database.Cseguridad set cuponacre = 1 where _id_cliente = 184;
    private function setcuponacre(){
        $sql = "UPDATE Cseguridad SET cupon_nombre = '{$this->formulario->Costumer->usercpn}' WHERE _id_cliente = '{$this->formulario->Costumer->profile->id}'";
        return $this->conn->query($sql)? true: false;
    }
    
    private function setOneCostumer(){
    $sql = "UPDATE clientes SET Codigo_postal='{$this->formulario->Costumer->Codigo_postal}', Colonia= '{$this->formulario->Costumer->Colonia}',
        Domicilio = '{$this->formulario->Costumer->Domicilio}', ciudad = '{$this->formulario->Costumer->ciudad}', estado='{$this->formulario->Costumer->estado}'
        WHERE _id = '{$this->formulario->Costumer->_id}'";
        return $this->conn->query($sql)? true: false;
    }

    private function getImporte(){
        $importe = 0.0;
        foreach($_SESSION["CarritoPrueba"] as $key=> $value){
            $importe += ($value["Cantidad"]*$value["Precio"]);
        }
        //Agregamos el costo de envio
        $importe += $_SESSION["Cenvio"]["costo"];
        return $importe;
    }

    private function deleteCarrito(){
        $sql = "DELETE FROM Carrito WHERE _clienteid = '{$this->formulario->Costumer->profile->id}'";
        return $this->conn->query($sql)? true: false;
    }

    private function setPedido(){
        $sql = "INSERT INTO Pedidos(_idCliente, Fecha, Importe, Acreditado, Enviado, GuiaEnvio, FormaPago, noPedido,cenvio,Servicio) values
        ('{$this->formulario->Costumer->Data["_id"]}','".date("Y-m-d")."', {$this->formulario->Costumer->Importe}, 
        0, 0,'','{$this->formulario->Costumer->value}','{$this->formulario->Costumer->noPedido["folio"]}',
        {$this->formulario->Costumer->Cenvio},'{$this->formulario->Costumer->Servicio}')";
        $this->conn->query($sql); 
        return $this->conn->last_id();
    }

    private function setPedido2($id_cliente, $Importe, $formaPago, $noPedido, $cenvio, $servicio, $facturacion, $_id_facturacion,$_id_domicilio, $acreditado=0, $descuento = 0, $paqueteria, $enviodias, $envioestimado, $alto, $largo, $ancho, $peso){
        $_id_facturacion = $facturacion == 1? $_id_facturacion:0;
        $sql = "INSERT INTO Pedidos (_idCliente, Fecha, Importe, Acreditado, Enviado, GuiaEnvio, FormaPago, noPedido, cenvio, Servicio, Facturacion, 
        _id_facturacion, _id_cdirecciones, archivoxml, archivopdf, comprobante, descuento, paqueteria, enviodias, Largo, Alto, Ancho, Peso, FechaEstimadaEnvio) values "
                . "( '$id_cliente','". date("Y-m-d") ."','$Importe',$acreditado,0,'','$formaPago','$noPedido','$cenvio','$servicio',$facturacion,
                $_id_facturacion, $_id_domicilio,'','','', $descuento, '$paqueteria', '$enviodias','$largo','$alto','$ancho','$peso','$envioestimado')";
        $this->conn->query($sql);
        return $this->conn->last_id();
    }

    private function setPedidosDetalles($id=null){
        $values = "";
        foreach($_SESSION["CarritoPrueba"] as $key => $value){
            $values .="('$id','{$value["imagenid"]}','{$value["Precio"]}','{$value["Cantidad"]}'),";
        }
        $values = trim($values,',');
        $sql = "INSERT INTO DetallesPedidos(_idPedidos, _idProducto, Importe, cantidad) values $values";
        return $this->conn->query($sql)? true: false;
    }

    private function setXML(){
        $objetoXML = new XMLWriter();
        // Estructura básica del XML
        //$objetoXML->openURI("../../../XML/certificado_prueba.xml");
        $objetoXML->openMemory();
            $objetoXML->setIndent(true);
            $objetoXML->setIndentString("\t");
            $objetoXML->startDocument('1.0', 'utf-8','yes');
            $objetoXML->startElement("P");

                $objetoXML->startElement("business");
                    $objetoXML->startElement("id_company");
                        $objetoXML->text($this->dataEmpresa["id_company"]);
                    $objetoXML->endElement(); // Final del nodo raíz, "id_company"        
                    $objetoXML->startElement("id_branch");
                        $objetoXML->text($this->dataEmpresa["id_branch"]);
                    $objetoXML->endElement(); // Final del nodo raíz, "id_branch"        
                    $objetoXML->startElement("user");
                        $objetoXML->text($this->dataEmpresa["user"]);
                    $objetoXML->endElement(); // Final del nodo raíz, "user"        
                    $objetoXML->startElement("pwd");
                        $objetoXML->text($this->dataEmpresa["pwd"]);
                    $objetoXML->endElement(); // Final del nodo raíz, "id_company"        
                $objetoXML->endElement(); // Final del nodo raíz, "business"

                $objetoXML->startElement("url");
                    $objetoXML->startElement("reference");
                        $objetoXML->text($this->formulario->Costumer->noPedido["folio"]); //aqui va el numero de orden como referencia
                    $objetoXML->endElement(); // Final del nodo raíz, "reference"        
                    $objetoXML->startElement("amount");
                        $objetoXML->text($this->formulario->Costumer->Importe); //ingresamos el pago con dos decimales
                    $objetoXML->endElement(); // Final del nodo raíz, "amount"        
                    $objetoXML->startElement("moneda");
                        $objetoXML->text("MXN"); //se especifica el tipo de moneda sin son pesos (MXN) o dolares (USD)
                    $objetoXML->endElement(); // Final del nodo raíz, "user"        
                    $objetoXML->startElement("canal");
                        $objetoXML->text("W"); //Este debe de ser siempre W
                    $objetoXML->endElement(); // Final del nodo raíz, "canal"        
                    $objetoXML->startElement("omitir_notif_default");
                        $objetoXML->text("1"); //0: Envia notificacion de cobro, 1: no envia notificacion de cobro
                    $objetoXML->endElement(); // Final del nodo raíz, "omitir_notif_default"
                    $objetoXML->startElement("st_correo");
                        $objetoXML->text("1"); //0: no se especifica el correo del cuenta habiente, 1: se especifica el correo del cuetna habiente
                    $objetoXML->endElement(); // Final del nodo raíz, "st_correo"
                    $objetoXML->startElement("mail_cliente");
                        //$objetoXML->text($this->formulario->Costumer->Data["correo"]); //0: no se especifica el correo del cuenta habiente, 1: se especifica el correo del cuetna habiente
                        $objetoXML->text($this->formulario->Costumer->profile->correo);
                    $objetoXML->endElement(); // Final del nodo raíz, "st_correo"
                $objetoXML->endElement(); // Final del nodo raíz, "url"

            $objetoXML->endElement(); // Final del nodo raíz, "P"

        $objetoXML->endDocument(); // Final del documento
        return $objetoXML->outputMemory(TRUE);
    }

    private function sendPost(){
        /*$url = "https://sandboxpo.mit.com.mx/gen"; //esta es url es para pruebas
        $Data0 = "SNDBX123";*/
        
        $url = "https://bc.mitec.com.mx/p/gen";
        $Data0 = "9265655359";
        $curl = curl_init();
        $encodedString = urlencode("<pgs><data0>$Data0</data0><data>$this->encryptedString</data></pgs>");
        
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,"xml=$encodedString");
        $respond = curl_exec($curl);
        curl_close($curl);
        return $respond;
    }

    private function getXML (){
        $array = array("res"=>false, "url"=>"");
        $ch = simplexml_load_string($this->response);
        if(strlen($ch->nb_response)== 0 ){
            $array["res"] = true;
            $array["url"] = $ch->nb_url;
            return  $array;
        }
    }

    private function getnoPedido(){
        $row = array();
        $sql = "Select * from Folios where tipo='PEDIDOS'";
        $this->conn->query($sql);
        if($this->conn->count_rows()!=0){
            $row = $this->conn->fetch();   
        }else{
            $row["folio"] = 1;
            $row["tipo"] = "PEDIDOS";
            $row["_id"] = $this->setnoPedido("I");
        }
        return $row;
    }

    private function setnoPedido($opc="U"){
        if($opc == "I"){
            $sql = "INSERT INTO Folios(folio,tipo) values('1','PEDIDOS')";
        }else{
            $this->formulario->Costumer->noPedido["folio"]++;
            $sql = "Update Folios SET folio = " .  $this->formulario->Costumer->noPedido["folio"]
            . " where _id = ".$this->formulario->Costumer->noPedido["_id"];
        }
        
        $this->conn->query($sql);
        return $opc = "I"? $this->conn->last_id():true;
    }
}

$app = new ProcesoCompra($array_principal);
$app->main();
