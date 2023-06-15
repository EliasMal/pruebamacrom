<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Pedidos{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $estatus = array("Por Acreditar", "Acreditado", "En preparacion", "En transito", "En proceso de Entrega", "Entregado", "Cancelado");
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
         
        switch ($this->formulario->pedidos->opc){
            case 'get':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["No_pedidos"] = $this->getNoPedidos($this->formulario->pedidos->find, $this->formulario->pedidos->Historico);
                $this->jsonData["Pedidos"] = $this->getPedidos($this->formulario->pedidos->x, $this->formulario->pedidos->y,
                 $this->formulario->pedidos->find, $this->formulario->pedidos->Historico);
                break;
            case 'getOne':
                $this->jsonData["Pedido"] = $this->getOnePedido();
                $this->jsonData["Pedido"]["isFileComprobante"] = strlen($this->jsonData["Pedido"]["comprobante"])>0? file_exists("../../../../../../Public/Comprobantes/{$this->jsonData["Pedido"]["comprobante"]}"):false;
                $this->jsonData["Detalles"] = $this->getPedidoDetalles();
                if($this->jsonData["Pedido"]["FormaPago"]=="Tarjeta"){
                    $this->jsonData["Tarjeta"] = $this->getTarjeta($this->formulario->pedidos->id);   
                }
                $this->jsonData["Bandera"] = 1 ;
            break;
        }
        print json_encode($this->jsonData);
    }

    

    private function getNoPedidos($find, $historico){
        $clausula = $historico? " ":"not";
        $array = array();
        $sql = "SELECT * FROM Pedidos as P inner join clientes as C
        on (P._idCliente = C._id) where (C.nombres like '%$find%' or C.Apellidos like '%$find%' or P.noPedido like '%$find%')
        and Acreditado $clausula in ('5','6')";
        $this->conn->query($sql);
        return $this->conn->count_rows();
    }

    private function getPedidos($x=0,$y=10, $find, $historico){
        $clausula = $historico? " ":"not";
        $array = array();
        $sql = "SELECT P._idPedidos, P.noPedido, P.Fecha, (P.Importe + P.cenvio) as Importe, P.Acreditado, P.FormaPago, 
         C.nombres, C.Apellidos, P.Facturacion FROM Pedidos as P 
        inner join clientes as C on (P._idCliente = C._id) WHERE (C.nombres like '%$find%' or C.Apellidos like '%$find%' or P.noPedido like '%$find%' )
        and Acreditado $clausula in ('5', '6') order by P._idPedidos Desc LIMIT $x,$y";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            $row["estatus"] = $this->estatus[$row["Acreditado"]];
            array_push($array,$row);
        }
        return $array;
    }

    private function getOnePedido(){
        $sql = "SELECT P._idPedidos, P.Fecha, P.cenvio, P.Servicio, P.Importe, P.Acreditado,  if(P.Acreditado=1, 'Acreditado','Por Acreditar') as Acreditadotxt,
        P.Enviado, P.GuiaEnvio, P.FormaPago, C.nombres, C.Apellidos, C.correo, P.Facturacion, P.archivoxml, P.archivopdf, P.noPedido, P.comprobante, 
        CD.Domicilio, CD.Codigo_postal, CD.Telefono, CD.Colonia, CD.Ciudad, CD.Estado, CD.numExt, CD.numInt, CD.Referencia,
         CF.UsoCFDI, CF.Descripción as Descripcion, F.Rfc, F.Razonsocial, F.Domicilio as FDomicilio, P.descuento 
        from Pedidos as P 
        inner join clientes as C on (P._idCliente = C._id)
        inner join Cdirecciones as CD on (P._id_cdirecciones = CD._id)
        left join Facturacion as F on (P._id_facturacion = F._id)
        left join usocfdi as CF on (F.cfdi = CF._id)
        where P._idPedidos = {$this->formulario->pedidos->id}";
        return $this->conn->fetch($this->conn->query($sql)) ;
    }

    private function getTarjeta($idPedido){
        $sql = "Select cc_type, cc_number from LogTerminal where _idPedidos = $idPedido";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function getPedidoDetalles(){
        $array = array();
        $sql = "SELECT DP._id, DP.Importe, DP.cantidad , P._id as parte, P.Clave, P.Producto
        FROM DetallesPedidos as DP 
        inner join Producto as P on (DP._idProducto = P._id) 
        where DP._idPedidos = {$this->formulario->pedidos->id} and DP.Estatus = 1";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            $row["imagen"] = file_exists("../../../../../../images/refacciones/{$row["parte"]}.png");
            array_push($array,$row);
        }
        return $array;
    }
}

$app = new Pedidos($array_principal);
$app->main();