<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/auth.php";
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
require_once "../../../tv-admin/asset/Clases/AESCrypto.php";
require_once '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

class ProcesoCompra {
    private $conn;
    private $AES;
    private $Http;
    private $response;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $cadenaOriginal = "";
    private $key = "DA6DAC61810C99C5017DE457560134C6"; // Semilla produccion
    private $encryptedString = "";
    private $dataEmpresa = array("id_company"=>"JGCA", "id_branch"=>"0001", "user"=>"JGCASIUS0", "pwd"=>"1S6VTAHKL");

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->AES = new AESCrypto();
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
                
                // INICIAR TRANSACCIÓN SEGURA
                $this->conn->query("START TRANSACTION");
                
                $subtotalReal = 0;
                foreach($_SESSION["CarritoPrueba"] as $value){
                    $subtotalReal += ($value["Cantidad"] * $value["Precio"]);
                }
                
                $idCupon = isset($this->formulario->Costumer->id_cupon) ? intval($this->formulario->Costumer->id_cupon) : 0;
                $descuentoReal = 0;
                $idCliente = intval($this->formulario->Costumer->profile->id);
                
                if($idCupon > 0){
                    $sql = "SELECT id, descuento, uso_unico, fecha_expiracion FROM cupones WHERE id = $idCupon AND activo = 1 LIMIT 1";
                    $this->conn->query($sql);

                    if($this->conn->count_rows() == 0){
                        $this->connRollback("Cupón inválido");
                        return;
                    }
                    $cupon = $this->conn->fetch();
                    
                    if(!empty($cupon["fecha_expiracion"]) && strtotime($cupon["fecha_expiracion"]) < time()){
                        $this->connRollback("Cupón expirado");
                        return;
                    }
                    
                    if($cupon["uso_unico"] == 1){
                        $sql = "SELECT id FROM cupones_usados WHERE id_cupon = $idCupon AND id_cliente = $idCliente LIMIT 1";
                        $this->conn->query($sql);
                        if($this->conn->count_rows() > 0){
                            $this->connRollback("Ya utilizaste este cupón");
                            return;
                        }
                    }
                    
                    $porcentaje = floatval($cupon["descuento"]);
                    $descuentoReal = $subtotalReal * ($porcentaje / 100);
                }
                
                $this->formulario->Costumer->descuento = $descuentoReal;
                $this->formulario->Costumer->Importe = ($subtotalReal + $this->formulario->Costumer->Cenvio->Costo) - $descuentoReal;

                if($this->formulario->Costumer->Importe != 0){
                    switch($this->formulario->Costumer->metodoPago){
                        case 'Deposito':
                        case 'Transferencia':
                            $id = $this->setPedido2(
                                $this->formulario->Costumer->profile->id, 
                                $this->formulario->Costumer->Subtotal, 
                                $this->formulario->Costumer->metodoPago, 
                                $this->formulario->Costumer->noPedido["folio"],
                                $this->formulario->Costumer->Cenvio->Costo, 
                                $this->formulario->Costumer->Cenvio->Servicio,
                                intval($this->formulario->Costumer->facturacion), 
                                isset($this->formulario->Costumer->dataFacturacion->data->_id)? $this->formulario->Costumer->dataFacturacion->data->_id:0,
                                $this->formulario->Costumer->dataDomicilio->data->_id,
                                0,
                                $this->formulario->Costumer->descuento,
                                $this->formulario->Costumer->Cenvio->paqueteria,
                                $this->formulario->Costumer->Cenvio->enviodias,
                                $this->formulario->Costumer->DiaEstimado,
                                $this->formulario->Costumer->Medidas->height,
                                $this->formulario->Costumer->Medidas->length,
                                $this->formulario->Costumer->Medidas->width,
                                $this->formulario->Costumer->Medidas->weight
                            );
                            
                            if($id){
                                if($this->setPedidosDetalles($id)){
                                    $this->actEXcompra(); // Resta stock súper segura
                                    
                                    unset($_SESSION["CarritoPrueba"]);
                                    unset($_SESSION["padlock"]);
                                    $this->deleteCarrito();
                                    $_SESSION["id_pedido"] = $id;
                                    
                                    if($idCupon > 0){
                                        $sql = "INSERT INTO cupones_usados (id_cupon, id_cliente, id_pedido) VALUES ($idCupon, $idCliente, $id)";
                                        $this->conn->query($sql);
                                    }
                                    
                                    // CONFIRMAR TRANSACCIÓN
                                    $this->conn->query("COMMIT");
                                    
                                    // Incrementar folio solo si la compra fue un éxito
                                    $this->incrementarFolio($this->formulario->Costumer->noPedido["folio"], $this->formulario->Costumer->noPedido["_id"]);
                                    
                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "Tu pedido se ha generado satisfactoriamente";
                                    $this->jsonData["Data"] = $id;       
                                    
                                    // ENVÍO DE CORREOS
                                    $this->enviarCorreoNotificacionAdmin($this->formulario->Costumer->metodoPago, $id, $this->formulario->Costumer->noPedido["folio"]);
                                    $this->enviarCorreoCompraAcreditadaCliente($_SESSION["usr"], $_SESSION["nombrecorto"], $id, $this->formulario->Costumer->noPedido["folio"], $this->formulario->Costumer->metodoPago);

                                } else {
                                    $this->connRollback("Error al guardar los detalles");
                                }
                            } else {
                                $this->connRollback("Error al generar tu pedido");
                            }
                            $_SESSION["Cenvio"]["costo"] = 0;
                            $_SESSION["Cenvio"]["Servicio"]= "";
                        break;

                        case 'Tarjeta':
                            $this->cadenaOriginal = $this->setXML();
                            $this->encryptedString = $this->AES->encriptar($this->cadenaOriginal, $this->key);
                            $this->response = $this->AES->desencriptar($this->sendPost(),$this->key);
                            $tempgetXml = $this->getXML();
                        
                            if($tempgetXml["res"]){
                                $id = $this->setPedido2(
                                    $this->formulario->Costumer->profile->id, 
                                    $this->formulario->Costumer->Subtotal, 
                                    $this->formulario->Costumer->metodoPago, 
                                    $this->formulario->Costumer->noPedido["folio"],
                                    $this->formulario->Costumer->Cenvio->Costo, 
                                    $this->formulario->Costumer->Cenvio->Servicio,
                                    intval($this->formulario->Costumer->facturacion), 
                                    isset($this->formulario->Costumer->dataFacturacion->data->_id)? $this->formulario->Costumer->dataFacturacion->data->_id:0,
                                    $this->formulario->Costumer->dataDomicilio->data->_id,
                                    0,
                                    $this->formulario->Costumer->descuento,
                                    $this->formulario->Costumer->Cenvio->paqueteria,
                                    $this->formulario->Costumer->Cenvio->enviodias,
                                    $this->formulario->Costumer->DiaEstimado,
                                    $this->formulario->Costumer->Medidas->height,
                                    $this->formulario->Costumer->Medidas->length,
                                    $this->formulario->Costumer->Medidas->width,
                                    $this->formulario->Costumer->Medidas->weight
                                );
                        
                                if($id){
                                    if($this->setPedidosDetalles($id)){
                                        $this->actEXcompra(); // Resta stock súper segura
                                        
                                        unset($_SESSION["CarritoPrueba"]);
                                        unset($_SESSION["padlock"]);
                                        $this->deleteCarrito();
                                        $_SESSION["id_pedido"] = $id;
                        
                                        if($idCupon > 0){
                                            $sql = "INSERT INTO cupones_usados (id_cupon, id_cliente, id_pedido) VALUES ($idCupon, $idCliente, $id)";
                                            $this->conn->query($sql);
                                        }
                        
                                        $this->conn->query("COMMIT");
                                        $this->incrementarFolio($this->formulario->Costumer->noPedido["folio"], $this->formulario->Costumer->noPedido["_id"]);
                        
                                        $this->jsonData["Bandera"] = 1;
                                        $this->jsonData["mensaje"] = "Pedido generado, redirigiendo al pago con tarjeta";
                                        $this->jsonData["data"] = $tempgetXml["url"];
                        
                                    }else{
                                        $this->connRollback("Error al generar detalles del pedido");
                                    }
                                }else{
                                    $this->connRollback("Error al generar el pedido");
                                }
                            }else{
                                $this->connRollback("Error: no se generó la liga para el cobro por tarjeta");
                            }
                        
                            $_SESSION["Cenvio"]["costo"] = 0;
                            $_SESSION["Cenvio"]["Servicio"]= "";
                        break;
                    }
                }
            break;
        }
        
        print json_encode($this->jsonData);
    }
    
    // Función para manejar el Rollback en caso de error
    private function connRollback($msj){
        $this->conn->query("ROLLBACK");
        $this->jsonData["Bandera"] = 0;
        $this->jsonData["mensaje"] = $msj;
        print json_encode($this->jsonData);
    }

    private function actEXcompra(){
        foreach($_SESSION["CarritoPrueba"] as $value){
            $idProducto = intval($value["imagenid"]);
            $cantidad = intval($value["Cantidad"]);
            
            $sqlCheck = "SELECT stock FROM Producto WHERE _id = $idProducto LIMIT 1";
            $this->conn->query($sqlCheck);
            
            if($this->conn->count_rows() == 0){
                $this->connRollback("Un producto ya no existe en el catálogo.");
                exit;
            }
            
            $rowCheck = $this->conn->fetch();
            if(intval($rowCheck['stock']) < $cantidad){
                $this->connRollback("El producto: " . $value["Producto"] . " ya no tiene suficiente inventario.");
                exit; 
            }

            // Si hay stock, lo restamos
            $sqlUpdate = "UPDATE Producto SET stock = stock - $cantidad WHERE _id = $idProducto";
            $this->conn->query($sqlUpdate);
        }
        return true;
    }

    private function getOneCostumer (){
        $sql = "SELECT * FROM clientes where Estatus = 1 and correo='{$this->formulario->Costumer->usr}'";
        $this->conn->query($sql);
        return $this->conn->fetch();
    }
    
    private function setOneCostumer(){
        $sql = "UPDATE clientes SET Codigo_postal='{$this->formulario->Costumer->Codigo_postal}', Colonia= '{$this->formulario->Costumer->Colonia}',
        Domicilio = '{$this->formulario->Costumer->Domicilio}', ciudad = '{$this->formulario->Costumer->ciudad}', estado='{$this->formulario->Costumer->estado}'
        WHERE _id = '{$this->formulario->Costumer->_id}'";
        return $this->conn->query($sql)? true: false;
    }

    private function deleteCarrito(){
        $sql = "DELETE FROM Carrito WHERE _clienteid = '{$this->formulario->Costumer->profile->id}'";
        return $this->conn->query($sql)? true: false;
    }

    private function setPedido2($id_cliente, $Importe, $formaPago, $noPedido, $cenvio, $servicio, $facturacion, $_id_facturacion,$_id_domicilio, $acreditado=0, $descuento = 0, $paqueteria, $enviodias, $envioestimado, $alto, $largo, $ancho, $peso){
        $_id_facturacion = $facturacion == 1? $_id_facturacion:0;
        $sql = "INSERT INTO Pedidos (_idCliente, Fecha, Importe, Acreditado, Enviado, GuiaEnvio, FormaPago, noPedido, cenvio, Servicio, Facturacion, 
        _id_facturacion, _id_cdirecciones, archivoxml, archivopdf, comprobante, descuento, paqueteria, enviodias, Largo, Alto, Ancho, Peso, FechaEstimadaEnvio) values "
                . "( '$id_cliente','". date("Y-m-d H:i:s") ."','$Importe',$acreditado,0,'','$formaPago','$noPedido','$cenvio','$servicio',$facturacion,
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
        $objetoXML->openMemory();
        $objetoXML->setIndent(true);
        $objetoXML->setIndentString("\t");
        $objetoXML->startDocument('1.0', 'utf-8','yes');
        $objetoXML->startElement("P");
            $objetoXML->startElement("business");
                $objetoXML->startElement("id_company"); $objetoXML->text($this->dataEmpresa["id_company"]); $objetoXML->endElement();        
                $objetoXML->startElement("id_branch"); $objetoXML->text($this->dataEmpresa["id_branch"]); $objetoXML->endElement();        
                $objetoXML->startElement("user"); $objetoXML->text($this->dataEmpresa["user"]); $objetoXML->endElement();        
                $objetoXML->startElement("pwd"); $objetoXML->text($this->dataEmpresa["pwd"]); $objetoXML->endElement();        
            $objetoXML->endElement(); 
            $objetoXML->startElement("url");
                $objetoXML->startElement("reference"); $objetoXML->text($this->formulario->Costumer->noPedido["folio"]); $objetoXML->endElement();        
                $objetoXML->startElement("amount"); $objetoXML->text($this->formulario->Costumer->Importe); $objetoXML->endElement();        
                $objetoXML->startElement("moneda"); $objetoXML->text("MXN"); $objetoXML->endElement();        
                $objetoXML->startElement("canal"); $objetoXML->text("W"); $objetoXML->endElement();        
                $objetoXML->startElement("omitir_notif_default"); $objetoXML->text("1"); $objetoXML->endElement(); 
                $objetoXML->startElement("st_correo"); $objetoXML->text("1"); $objetoXML->endElement(); 
                $objetoXML->startElement("mail_cliente"); $objetoXML->text($this->formulario->Costumer->profile->correo); $objetoXML->endElement(); 
            $objetoXML->endElement(); 
        $objetoXML->endElement(); 
        $objetoXML->endDocument(); 
        return $objetoXML->outputMemory(TRUE);
    }

    private function sendPost(){
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
            $sql = "INSERT INTO Folios(folio,tipo) values('1','PEDIDOS')";
            $this->conn->query($sql);
            $row["folio"] = 1;
            $row["tipo"] = "PEDIDOS";
            $row["_id"] = $this->conn->last_id();
        }
        return $row;
    }

    private function incrementarFolio($folio_actual, $folio_id){
        $nuevo_folio = intval($folio_actual) + 1;
        $sql = "UPDATE Folios SET folio = $nuevo_folio where _id = $folio_id";
        return $this->conn->query($sql);
    }

    private function enviarCorreoNotificacionAdmin($metodo_pago, $id_pedido, $folio_pedido){
        $mail = new PHPMailer; $mail->isSMTP(); $mail->SMTPDebug = 0; $mail->Host = 'smtp.hostinger.com'; $mail->Port = 587; $mail->SMTPAuth = true; $mail->Username = 'ventasweb@macromautopartes.com'; $mail->Password = 'jSJLK6AqN%fwUOskf5@R'; $mail->setFrom('ventasweb@macromautopartes.com', 'Macrom Autopartes');
        $mail->addAddress('ventasweb@macromautopartes.com', 'Ventas'); $mail->addAddress('web.tsuruvolks@gmail.com', 'Ventas web');
        $mail->Subject = 'Nueva Compra ('.$metodo_pago.') - Folio: '.$folio_pedido; $mail->IsHTML(true); $mail->CharSet = 'utf-8';

        $detalles_resumen = "";
        $sql = "SELECT DP.cantidad, P.Producto, P.Clave as SKU FROM DetallesPedidos DP INNER JOIN Producto P ON DP._idProducto = P._id WHERE DP._idPedidos = $id_pedido";
        $this->conn->query($sql);
        while($row = $this->conn->fetch()){
            $detalles_resumen .= "<li style='margin-bottom: 5px;'><b>{$row['cantidad']}x</b> {$row['Producto']} <span style='color:#777; font-size:12px;'>(SKU: {$row['SKU']})</span></li>";
        }

        $sql_header = "SELECT paqueteria, Servicio FROM Pedidos WHERE _idPedidos = $id_pedido LIMIT 1";
        $this->conn->query($sql_header);
        $row_header = $this->conn->fetch();

        $aviso_admin = "";
        if($row_header["paqueteria"] == "Acordar con el negocio" || $row_header["Servicio"] == "Envío por paqueteria(Por Cotizar)"){
            $aviso_admin = "
            <div style='background-color: #fff3cd; color: #856404; padding: 15px; border-left: 5px solid #ffeeba; margin: 20px 0; border-radius: 4px;'>
                <h3 style='margin-top:0;'>⚠️ ATENCIÓN: PEDIDO VOLUMINOSO</h3>
                <p style='margin-bottom:0;'>El cliente <strong>ACEPTÓ</strong> los términos de envío pendiente. Es necesario contactarlo para cotizar la paqueteria y cobrar el envío por separado.</p>
            </div>";
        }

        $mail->Body ="
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #de0007;'>Nueva compra registrada en la web</h2>
            <p><strong>Folio de seguimiento:</strong> $folio_pedido</p>
            <p><strong>Método de pago:</strong> $metodo_pago</p>
            
            $aviso_admin

            <h3 style='border-bottom: 1px solid #ccc; padding-bottom: 5px;'>Resumen de Artículos:</h3>
            <ul style='list-style-type: square;'>
                $detalles_resumen
            </ul>
            
            <p style='margin-top: 30px; font-size: 13px; color: #666;'><em>* Ingresa al panel de administración para ver los datos completos.</em></p>
        </div>";

        $mail->send();
    }

    private function enviarCorreoCompraAcreditadaCliente($destinatario, $nombre, $id_pedido, $folio_pedido, $formaPago){
        $mail = new PHPMailer; $mail->isSMTP(); $mail->SMTPDebug = 0; $mail->Host = 'smtp.hostinger.com'; $mail->Port = 587; $mail->SMTPAuth = true; $mail->Username = 'ventasweb@macromautopartes.com'; $mail->Password = 'jSJLK6AqN%fwUOskf5@R'; $mail->setFrom('ventasweb@macromautopartes.com', 'Macrom Autopartes');
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = 'Compra Macromautopartes - Folio: '.$folio_pedido; $mail->IsHTML(true); $mail->CharSet = 'utf-8';

        $detalles_html = "";
        $total_importe = 0;
        
        $sql = "SELECT DP.cantidad, DP.Importe, P._id, P.Producto, P.Clave as SKU FROM DetallesPedidos DP INNER JOIN Producto P ON DP._idProducto = P._id WHERE DP._idPedidos = $id_pedido";
        $this->conn->query($sql);
        
        while($row = $this->conn->fetch()){
            $subtotal_row = floatval($row["Importe"]) * intval($row["cantidad"]);
            $total_importe += $subtotal_row;
            
            $precio_unitario = number_format(floatval($row["Importe"]), 2);
            $subtotal_formato = number_format($subtotal_row, 2);
            $img_url = "https://macromautopartes.com/images/refacciones/".$row["_id"].".webp";

            $detalles_html .= '
            <tr>
                <td style="padding: 15px 5px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top;">
                    <img src="'.$img_url.'" style="width: 50px; height: 50px; object-fit: contain; float: left; margin-right: 10px; border-radius: 5px; border: 1px solid #ddd;" alt="Producto">
                    <strong style="display: block; font-size: 13px; color: #333; margin-bottom: 3px;">'.$row["Producto"].'</strong>
                    <span style="font-size: 11px; color: #888;">SKU: '.$row["SKU"].'</span>
                </td>
                <td style="padding: 15px 5px; border-bottom: 1px solid #eee; text-align: center; vertical-align: middle;">'.$row["cantidad"].'</td>
                <td style="padding: 15px 5px; border-bottom: 1px solid #eee; text-align: right; vertical-align: middle; color:#555;">$'.$precio_unitario.'</td>
                <td style="padding: 15px 5px; border-bottom: 1px solid #eee; text-align: right; vertical-align: middle; font-weight: bold; color:#000;">$'.$subtotal_formato.'</td>
            </tr>';
        }

        $sql_header = "SELECT Importe, cenvio, descuento, Servicio, paqueteria FROM Pedidos WHERE _idPedidos = $id_pedido LIMIT 1";
        $this->conn->query($sql_header);
        $row_header = $this->conn->fetch();
        
        $subtotal_bd = floatval($row_header["Importe"]);
        $costo_envio_num = floatval($row_header["cenvio"]);
        $descuento_num = floatval($row_header["descuento"]);
        
        $gran_total = $subtotal_bd + $costo_envio_num - $descuento_num;

        $importe_total = number_format($gran_total, 2);
        $costo_envio = number_format($costo_envio_num, 2);
        $descuento = number_format($descuento_num, 2);
        $servicio_envio = $row_header["Servicio"];
        $aviso_envio_html = "";

        if($row_header["paqueteria"] == "Acordar con el negocio" || $servicio_envio == "Envío por paqueteria(Por Cotizar)"){
            $aviso_envio_html = '
            <div style="background-color: #fff3cd; border-left: 5px solid #ffeeba; padding: 15px; margin: 20px 0; text-align: left; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">⚠️ AVISO IMPORTANTE: ENVÍO PENDIENTE DE PAGO</h3>
                <p style="margin: 5px 0; color: #856404; font-size: 14px;">Tal como aceptaste durante tu compra, el pago realizado <strong>CUBRE ÚNICAMENTE EL COSTO DE LAS REFACCIONES</strong>.</p>
                <p style="margin: 5px 0; color: #856404; font-size: 14px;">El costo de envío <strong>NO ESTÁ INCLUIDO</strong>. Nos pondremos en contacto contigo para cotizar la paqueteria ideal y acordar el pago del envío.</p>
            </div>';
        }

        $info_bancaria_html = "";
        if($formaPago == 'Transferencia'){
            $info_bancaria_html = '
            <div style="background-color: #fff3f3; border-left: 5px solid #de0007; padding: 20px; margin: 25px 0; text-align: left; border-radius: 0 5px 5px 0;">
                <h3 style="margin: 0 0 10px 0; color: #de0007; font-size: 18px;">Datos para Transferencia (SPEI)</h3>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>Banco:</strong> Santander</p>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>A nombre de:</strong> Néstor Omar Lara Galindo</p>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>No. Cuenta:</strong> 65-50611157-9</p>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>CLABE:</strong> 014090655061115796</p>
                <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;"><em>* Usa el folio <strong>'.$folio_pedido.'</strong> como concepto de pago.</em></p>
            </div>';
        } else if($formaPago == 'Deposito'){
            $info_bancaria_html = '
            <div style="background-color: #fff3f3; border-left: 5px solid #de0007; padding: 20px; margin: 25px 0; text-align: left; border-radius: 0 5px 5px 0;">
                <h3 style="margin: 0 0 10px 0; color: #de0007; font-size: 18px;">Datos para Depósito (OXXO/Ventanilla)</h3>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>Banco:</strong> Santander</p>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>A nombre de:</strong> Néstor Omar Lara Galindo</p>
                <p style="margin: 5px 0; color: #333; font-size: 15px;"><strong>No. Tarjeta:</strong> 5579-0700-7328-2744</p>
            </div>';
        }

        $mail->Body ='
        <html lang="es">
        <body style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px;">
            <div style="width:100%; max-width: 600px; margin: 0 auto; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                
                <header style="background-color: #fff; padding: 0;">
                    <img style="width:100%; display:block;" src="https://macromautopartes.com/images/icons/CRcabecera.png">
                </header>
                
                <section style="padding: 30px; text-align: center;">
                    <h1 style="color:#de0007; font-size:26px; margin: 0 0 15px 0;">¡Pedido Registrado!</h1>
                    <p style="color:#444; font-size: 16px; line-height: 1.6; margin: 0;">
                        Hola <strong>'.$nombre.'</strong>, hemos recibido tu solicitud de pedido.<br>
                        Tu folio de seguimiento es el: <strong style="color:#de0007;">'.$folio_pedido.'</strong>.
                    </p>

                    '.$aviso_envio_html.'

                    '.$info_bancaria_html.'

                </section>

                <section style="padding: 0 30px 30px 30px;">
                    <h3 style="color: #222; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 10px; font-size:18px;">Resumen de Compra</h3>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 400px;">
                            <thead>
                                <tr style="background-color: #fafafa; color: #555;">
                                    <th style="padding: 12px 5px; border-bottom: 2px solid #eee; text-align: left;">Artículo</th>
                                    <th style="padding: 12px 5px; border-bottom: 2px solid #eee; text-align: center;">Cant.</th>
                                    <th style="padding: 12px 5px; border-bottom: 2px solid #eee; text-align: right;">Precio</th>
                                    <th style="padding: 12px 5px; border-bottom: 2px solid #eee; text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                '.$detalles_html.'
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="width: 100%; text-align: right; padding-top: 20px; font-size: 14px; color: #555;">
                        <p style="margin: 5px 0;">Envío ('.$servicio_envio.'): <strong>$'.$costo_envio.'</strong></p>
                        <p style="margin: 5px 0;">Descuento: <strong style="color:#de0007;">-$'.$descuento.'</strong></p>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                        <h2 style="color: #000; margin: 10px 0 0 0; font-size: 22px;">Total: <span style="color:#de0007;">$'.$importe_total.'</span></h2>
                    </div>
                </section>

                <footer style="background-color: #fff; padding: 0;">
                    <img style="width:100%; display:block;" src="https://macromautopartes.com/images/icons/CRPie-pagina.png">
                </footer>
            </div>
        </body>
        </html>';
        $mail->send();
    }
}

$app = new ProcesoCompra($array_principal);
$app->main();
?>