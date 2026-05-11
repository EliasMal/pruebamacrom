<?php
include("../Clases/ConexionMySQL.php");
include("../Clases/AESCrypto.php");
include("../Clases/dbconectar.php");
include('../../../vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;


Class Endpoint{
    private $key = "DA6DAC61810C99C5017DE457560134C6";
    private $AES;
    private $respuesta;
    private $xml;
    private $conn;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->AES = new AESCrypto();
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main (){
        if($_POST){
            /* LOG de Recepción */
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "--------------------------\n");
            fwrite($filelog, $_REQUEST["strResponse"]);
            fwrite($filelog, "\nRespuesta en claro--------\n");
            
            $this->respuesta = $this->AES->desencriptar($_REQUEST["strResponse"],$this->key);
            $this->xml = simplexml_load_string($this->respuesta);
            
            fwrite($filelog, $this->respuesta);
            fwrite($filelog, "\n--------------------------\n");
            fwrite($filelog, $this->xml->response);
            
            $id = $this->getPedidoId();
            $userid = $this->getPedidoUserId();
            $datauser = $this->getUser($userid);
            
            if($this->xml->response == "approved"){

                fwrite($filelog, "\nObtengo el id del pedido: " . $id . "\n");
                
                if($this->pedidoYaAcreditado($id)){
                    fwrite($filelog, "Pedido ya acreditado previamente\n");
                    fclose($filelog);
                    exit;
                }
                
                $this->conn->query("START TRANSACTION");
                
                //pedido como pagado (1)
                $this->setPedido($id, 1);
                
                $this->DeleteCarritoYEnviarCorreos($datauser, $id, $this->xml->reference);

                $this->conn->query("COMMIT");
            }else{
                // PAGO DECLINADO
                $this->conn->query("START TRANSACTION");
                
                // pedido como Cancelado (6)
                $this->setPedido($id, 6);
                
                $this->restaurarInventario($id);
                
                $this->conn->query("COMMIT");
                fwrite($filelog, "\nPago declinado. Inventario restaurado.\n");
            }
            fwrite($filelog, "\n" . $this->setLogTerminal($id) . "\n");
            fclose($filelog);
 
        }else{
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "no se recibio ningun post");
            fclose($filelog);
        }
    }

    private function restaurarInventario($pedidoId){
        $sql = "SELECT _idProducto, cantidad FROM DetallesPedidos WHERE _idPedidos = $pedidoId";
        $result = $this->conn->query($sql);

        while($row = $this->conn->fetch($result)){
            $idProducto = intval($row["_idProducto"]);
            $cantidad   = intval($row["cantidad"]);

            $update = "UPDATE Producto SET stock = stock + $cantidad WHERE _id = $idProducto";
            $this->conn->query($update);
        }
        return true;
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
    
    private function pedidoYaAcreditado($id){
        $sql = "SELECT Acreditado FROM Pedidos WHERE _idPedidos = $id";
        $res = $this->conn->fetch($this->conn->query($sql));
        return ($res && $res["Acreditado"] == 1);
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
        return $this->conn->query($sql)? "Log Terminal Guardado Correctamente":"Error en la insercion $sql";
    }

    private function DeleteCarritoYEnviarCorreos($user, $id_pedido, $folio_pedido){
        
        $detalles_html = "";
        $detalles_resumen_admin = "";
        $total_importe = 0;
        $sqlDetalles = "SELECT DP.cantidad, DP.Importe, P._id, P.Producto, P.Clave as SKU FROM DetallesPedidos DP INNER JOIN Producto P ON DP._idProducto = P._id WHERE DP._idPedidos = $id_pedido";
        $this->conn->query($sqlDetalles);
        
        while($row = $this->conn->fetch()){
            $detalles_resumen_admin .= "<li style='margin-bottom: 5px;'><b>{$row['cantidad']}x</b> {$row['Producto']} <span style='color:#777; font-size:12px;'>(SKU: {$row['SKU']})</span></li>";
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
        $aviso_envio_admin = "";
        $aviso_envio_cliente = "";
        
        if($row_header["paqueteria"] == "Acordar con el negocio" || $servicio_envio == "Envío por paqueteria(Por Cotizar)"){
            
            $aviso_envio_admin = "
            <div style='background-color: #fff3cd; color: #856404; padding: 15px; border-left: 5px solid #ffeeba; margin: 20px 0; border-radius: 4px;'>
                <h3 style='margin-top:0;'>⚠️ ATENCIÓN: PEDIDO VOLUMINOSO</h3>
                <p style='margin-bottom:0;'>El cliente <strong>ACEPTÓ</strong> los términos de envío pendiente. Es necesario contactarlo para cotizar la paqueteria y cobrar el envío.</p>
            </div>";
            
            $aviso_envio_cliente = '
            <div style="background-color: #fff3cd; border-left: 5px solid #ffeeba; padding: 15px; margin: 20px 0; text-align: left; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">⚠️ AVISO IMPORTANTE: ENVÍO PENDIENTE DE PAGO</h3>
                <p style="margin: 5px 0; color: #856404; font-size: 14px;">Tal como aceptaste durante tu compra, el pago que se acaba de cobrar a tu tarjeta <strong>CUBRE ÚNICAMENTE EL COSTO DE LAS REFACCIONES</strong>.</p>
                <p style="margin: 5px 0; color: #856404; font-size: 14px;">El costo de envío <strong>NO ESTÁ INCLUIDO</strong>. Un asesor se pondrá en contacto contigo para cotizar la pqueteria ideal y acordar el pago del envío.</p>
            </div>';
        }

        // ==========================================
        // ENVÍO DE CORREO A ADMINISTRADORES
        // ==========================================
        $mailAdmin = new PHPMailer;
        $mailAdmin->isSMTP(); $mailAdmin->SMTPDebug = 0; $mailAdmin->Host = 'smtp.hostinger.com'; $mailAdmin->Port = 587; 
        $mailAdmin->SMTPAuth = true; $mailAdmin->Username = 'ventasweb@macromautopartes.com'; $mailAdmin->Password = 'jSJLK6AqN%fwUOskf5@R'; 
        $mailAdmin->setFrom('ventasweb@macromautopartes.com', 'Macrom Autopartes');
        $mailAdmin->addAddress('ventasweb@macromautopartes.com', 'Ventas');
        $mailAdmin->addAddress('web.tsuruvolks@gmail.com', 'Ventas web');
        $mailAdmin->Subject = 'Nueva Compra Tarjeta MIT - Folio: '.$folio_pedido;
        $mailAdmin->IsHTML(true); $mailAdmin->CharSet = 'utf-8';
        
        $mailAdmin->Body ="
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #10b981;'>¡Éxito! Nueva compra con tarjeta</h2>
            <p><strong>Folio de seguimiento:</strong> $folio_pedido</p>
            
            $aviso_envio_admin

            <h3 style='border-bottom: 1px solid #ccc; padding-bottom: 5px;'>Resumen de Artículos:</h3>
            <ul style='list-style-type: square;'>
                $detalles_resumen_admin
            </ul>
            
            <p style='margin-top: 30px; font-size: 13px; color: #666;'><em>* Ingresa al panel de administración para procesar la orden.</em></p>
        </div>";
        $mailAdmin->send();


        // ==========================================
        // ENVÍO DE CORREO AL CLIENTE
        // ==========================================
        $destinatario = $user["correo"];
        $nombre = $user["nombres"];
        
        $mailCliente = new PHPMailer;
        $mailCliente->isSMTP(); $mailCliente->SMTPDebug = 0; $mailCliente->Host = 'smtp.hostinger.com'; $mailCliente->Port = 587; 
        $mailCliente->SMTPAuth = true; $mailCliente->Username = 'ventasweb@macromautopartes.com'; $mailCliente->Password = 'jSJLK6AqN%fwUOskf5@R'; 
        $mailCliente->setFrom('ventasweb@macromautopartes.com', 'Macrom Autopartes');
        $mailCliente->addAddress($destinatario, $nombre);
        $mailCliente->Subject = 'Pago Exitoso Macromautopartes - Folio: '.$folio_pedido;
        $mailCliente->IsHTML(true); $mailCliente->CharSet = 'utf-8';
        
        $mailCliente->Body ='
        <html lang="es">
        <body style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px;">
            <div style="width:100%; max-width: 600px; margin: 0 auto; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <header style="background-color: #fff; padding: 0;">
                    <img style="width:100%; display:block;" src="https://macromautopartes.com/images/icons/CRcabecera.png">
                </header>
                
                <section style="padding: 30px; text-align: center;">
                    <h1 style="color:#10b981; font-size:26px; margin: 0 0 15px 0;">¡Pago Procesado con Éxito!</h1>
                    <p style="color:#444; font-size: 16px; line-height: 1.6; margin: 0;">
                        Hola <strong>'.$nombre.'</strong>, hemos cobrado exitosamente el cargo a tu tarjeta.<br>
                        Tu pedido ya está siendo empacado y saldrá muy pronto a su destino.<br>
                        Tu folio de seguimiento es el: <strong style="color:#de0007;">'.$folio_pedido.'</strong>.
                    </p>
                    
                    '.$aviso_envio_cliente.'
                    
                </section>

                <section style="padding: 0 30px 30px 30px;">
                    <h3 style="color: #222; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 10px; font-size:18px;">Resumen de tu Pedido</h3>
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
                        <h2 style="color: #000; margin: 10px 0 0 0; font-size: 22px;">Total Pagado: <span style="color:#de0007;">$'.$importe_total.'</span></h2>
                    </div>
                </section>

                <footer style="background-color: #fff; padding: 0;">
                    <img style="width:100%; display:block;" src="https://macromautopartes.com/images/icons/CRPie-pagina.png">
                </footer>
            </div>
        </body>
        </html>';
        
        $mailCliente->send();
        
        $sqlDelete = "DELETE FROM Carrito WHERE _clienteid = ".$user["_id"];
        return $this->conn->query($sqlDelete);
    }
}

$app = new Endpoint($array_principal);
$app->main();
?>