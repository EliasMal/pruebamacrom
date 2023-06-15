<?php
    /*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of home
 *
 * @author francisco
 */
session_name("loginCliente");
session_start();
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

date_default_timezone_set('America/Mexico_City');

class Profile{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $redpack;
    private $estatus = array("Por Acreditar", "Acreditado", "En preparacion", "En transito", "En proceso de Entrega", "Entregado", "Cancelado");

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        
    }

    public function __destruct() {
        unset($this->conn);
    }

    private function getOneCliente ($tipo) {
        switch ($tipo){
            case 'Session':
            default:
                $sql = "select C._id, C.nombres, C.Apellidos, C.correo, C.telefono, CS._id as _id_seguridad from clientes as C 
                inner join Cseguridad as CS on (CS._id_cliente = C._id) where CS.username = '{$_SESSION["usr"]}'";
            break;
            case 'Direcciones':
                $sql = "select C._id, C.Domicilio, C.Colonia, C.Telefono, C.Codigo_postal, C.ciudad, C.estado from clientes as C 
                inner join Cseguridad as CS on (CS._id_cliente = C._id) where CS.username = '{$_SESSION["usr"]}'";
            break;
        }
        
        return  $this->conn->fetch($this->conn->query($sql));
    }

    private function get_Domicilios($id_cliente){
        $array = array();
        $sql = "SELECT * FROM Cdirecciones where _id_cliente = $id_cliente and Estatus = 1 order by Predeterminado desc";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function get_disabledDomicilioPredeterminado(){
        $sql = "SELECT * FROM Cdirecciones where Predeterminado = 1";
        $id = $this->conn->query($sql);
        if($this->conn->count_rows() > 0){
            while($row = $this->conn->fetch($id)){
                $sql = "UPDATE Cdirecciones SET Predeterminado = 0 where _id = {$row["_id"]}";
                $this->conn->query($sql);
            }
            
        }
    }

    private function get_OneDomicilio($id){
        $sql = "SELECT * from Cdirecciones where _id = $id";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function set_Domicilios($data, $opc){
        switch($opc){
            case 'add':
                $sql = "INSERT INTO Cdirecciones (Domicilio, Codigo_postal, Telefono, Colonia, Ciudad, Estado, Predeterminado, Estatus, _id_cliente, numExt, numInt, Referencia) "
                ."values ('$data->Domicilio','$data->Codigo_postal','$data->Telefono','$data->Colonia','$data->Ciudad','$data->Estado',$data->Predeterminado,$data->Estatus,$data->id,"
                ."'$data->numExt','$data->numInt','$data->Referencia')";
            break;
            case 'delete':
                $sql = "DELETE FROM Cdirecciones where _id = $data->id";    
            break;
            case 'set':
                $sql = "UPDATE Cdirecciones SET Predeterminado = 1 where _id = $data->id_domicilio";
            break;
            case 'save':
                $sql = "UPDATE Cdirecciones SET Domicilio='{$data->Domicilio}', Codigo_postal = '$data->Codigo_postal', Telefono='$data->Telefono', "
                    . "Colonia = '$data->Colonia', Ciudad = '$data->Ciudad', Estado = '$data->Estado', numExt = '$data->numExt', "
                    . "numInt = '$data->numInt', Referencia = '$data->Referencia' where _id= $data->_id";
            break;
        }
        return $this->conn->query($sql)? true : false; 
    }

    private function set_cliente(){
        $sql = "UPDATE clientes SET nombres = '{$this->formulario->profile->data->nombres}', Apellidos = '{$this->formulario->profile->data->Apellidos}', "
             . "correo= '{$this->formulario->profile->data->correo}', telefono='{$this->formulario->profile->data->telefono}' "
             . "where _id = {$this->formulario->profile->data->_id}";
        return $this->conn->query($sql)? true: false;
    }

    private function set_DomiciliopredeterminadoCliente($data, $Domicilio){
        $sql = "UPDATE clientes SET Domicilio = '{$Domicilio["Domicilio"]}', Colonia = '{$Domicilio["Colonia"]}'," 
            . "Codigo_postal = '{$Domicilio["Codigo_postal"]}',"
            ."ciudad = '{$Domicilio["Ciudad"]}', estado = '{$Domicilio["Estado"]}', telefono = '{$Domicilio["Telefono"]}' where _id = $data->id";
        return $this->conn->query($sql)? true: false;   
    }

    private function get_Password(){
        $sql = "SELECT password from Cseguridad where _id = {$this->formulario->profile->data->_id_seguridad}";
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["password"];
    }

    private function set_Password(){
        $sql = "UPDATE Cseguridad set password = SHA('{$this->formulario->profile->data->Nuevapass}') 
                WHERE _id= {$this->formulario->profile->data->_id_seguridad}";
        // 
        return $this->conn->query($sql)? true:false;
    }

    private function get_Usocfdi(){
        $array = array();
        $sql = "SELECT _id, UsoCFDI, Descripción as Descripcion from usocfdi";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function get_Estados(){
        $array = array();
        $sql = "SELECT _id, estados, descripcion as Descripcion from Estados";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }
    
    private function get_Carrito(){
        $array = array();
        $sql = "SELECT _clienteid, Clave, No_parte, Cantidad, Precio, Producto as _producto FROM Carrito";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }


    private function set_Facturacion($opc, $id = null){
        switch($opc){
            case 'add':
               $sql = "INSERT INTO Facturacion(_id_cliente, Rfc, Razonsocial, Domicilio, Codigo_postal, Colonia, Ciudad,  Estado, Estatus, cfdi, Predeterminado, Actividad) "
                        . "values ('{$this->formulario->profile->data->_id_cliente}','{$this->formulario->profile->data->Rfc}','{$this->formulario->profile->data->Razonsocial}', "
                        . "'{$this->formulario->profile->data->Domicilio}','{$this->formulario->profile->data->Codigo_postal}','{$this->formulario->profile->data->Colonia}', "
                        . "'{$this->formulario->profile->data->Ciudad}','{$this->formulario->profile->data->Estado}',{$this->formulario->profile->data->Estatus}, "
                        . "'{$this->formulario->profile->data->cfdi}',{$this->formulario->profile->data->Predeterminado},'{$this->formulario->profile->data->Actividad}')";
            break;
            case 'save':
                $sql = "UPDATE Facturacion SET Rfc = '{$this->formulario->profile->data->Rfc}', "
                        . " Razonsocial = '{$this->formulario->profile->data->Razonsocial}', Domicilio = '{$this->formulario->profile->data->Domicilio}', "
                        . " Codigo_postal = '{$this->formulario->profile->data->Codigo_postal}', Colonia = '{$this->formulario->profile->data->Colonia}', "
                        . " Ciudad = '{$this->formulario->profile->data->Ciudad}', Estado = '{$this->formulario->profile->data->Estado}', "
                        . " cfdi = '{$this->formulario->profile->data->cfdi}', Actividad = '{$this->formulario->profile->data->Actividad}'"
                        . " where _id = {$this->formulario->profile->data->_id}";
            break;
            case 'del':
                $sql = "DELETE FROM Facturacion where _id = $id";
            break;
            case 'pre':
                $sql = "UPDATE Facturacion SET Predeterminado = 1 where _id = $id ";
            break;
        }
        

        return $this->conn->query($sql)? true:false;
    }

    private function get_datosFactuDeter(){
        $sql = "Select _id from Facturacion where Predeterminado = 1";
        $id = $this->conn->query($sql);
        if($this->conn->count_rows() > 0){
            $row = $this->conn->fetch($id);
            $sql = "UPDATE Facturacion SET Predeterminado = 0 where _id = {$row["_id"]}";
            $this->conn->query($sql);
        }
    }

    private function get_dataOneFacturacion($id){
        $sql = "Select * from Facturacion where _id = $id";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function get_dataFacturacion($_id_cliente){
        $array = array();
        $sql = "SELECT F.*, U.Descripción as Descripcion FROM Facturacion as F " 
            . "inner join usocfdi as U on (F.cfdi = U._id) where "
            . "F._id_cliente = $_id_cliente and F.Estatus = 1  order by F.Predeterminado asc";
        $id = $this->conn->query($sql) or die($this->conn->error);
        while($row = $this->conn->fetch($id)){
            array_push($array,$row);
        }
        return $array;
    }

    private function get_DetallesMispedidos($_idPedido, $limit = false){
        $array = array();
        $sql = "select DP.* , P.Producto, P.Clave, P.No_parte from DetallesPedidos as DP 
                inner join Producto as P on (DP._idProducto = P._id)
                where DP._idPedidos = $_idPedido ";
        $sql .= $limit? " LIMIT 0, 2":"";        
                $id = $this->conn->query($sql);
                
                while($row = $this->conn->fetch($id)){
                    $row["imagen"] = file_exists("../../../images/refacciones/{$row["Clave"]}.png");
                    array_push($array,$row);
                }
        return $array;
    }

    private function get_CountMisPedidos($_id_cliente){
        $sql = "SELECT * FROM Pedidos WHERE _idCliente = $_id_cliente";
        $this->conn->query($sql);
        return $this->conn->count_rows();
    }

    private function get_dataMispedidos($_id_cliente,$x=0,$y=5){
        $array = array();
        $sql = "SELECT P.*, (P.Importe + P.cenvio - P.descuento) as Totalpedido, concat(C.nombres, ' ', C.Apellidos ) as nombreCliente, F.*, CF.UsoCFDI, CF.Descripción as Descripcion from Pedidos as P 
        inner join clientes as C on (C._id = P._idCliente) 
        left join Facturacion as F on (P._id_facturacion = F._id)
        left join usocfdi as CF on (F.cfdi = CF._id)
        where P._idCliente = $_id_cliente order by P.noPedido desc limit $x,$y";
        $id = $this->conn->query($sql);
        
        while($row = $this->conn->fetch($id)){
            $row["Detalles"] = $this->get_DetallesMispedidos($row["_idPedidos"],true);
            $row["Estado"] = $this->estatus[$row["Acreditado"]];
            array_push($array,$row);
        }
        return $array;
    }

    private function get_Mipedido($_idpedido){
        $sql = "SELECT P.*, (P.Importe + P.cenvio - P.descuento ) as Totalpedido, 
        concat(C.nombres, ' ', C.Apellidos ) as nombreCliente,
        P.descuento from Pedidos as P 
        inner join clientes as C on (C._id = P._idCliente) where _idPedidos = $_idpedido";
        $row = $this->conn->fetch($this->conn->query($sql));
        $row["Detalles"] = $this->get_DetallesMispedidos($_idpedido);
        $row["isFileComprobante"] = strlen($row["comprobante"])>0? file_exists("../../../Public/Comprobantes/{$row["comprobante"]}"):false;
        return $row;
    } 

    private function getdetalsComprobanteMipedido($id){
        $sql = "SELECT comprobante  from Pedidos where _idPedidos=$id";
        return $this->conn->fetch($this->conn->query($sql));
    }
    private function setdetalsComprobanteMipedido($id){
        $sql ="UPDATE Pedidos SET comprobante='' where _idPedidos = $id";
        return $this->conn->query($sql)? true:false;
    }

    private function deleteComprobante ($comprobante){
         $url = "../../../Public/Comprobantes/".$comprobante['comprobante'];
        return unlink($url);
    }

    private function setMonedero($data){
        $sql = "INSERT INTO Monedero(_id_cliente, Descripcion, Importe, movimiento) 
        values({$data["_idCliente"]},'Deposito monedero cancelacion pedido No. {$data["noPedido"]}', ". ($data["Importe"]+$data["cenvio"]) .", 1)";
        return $this->conn->query($sql);
    }

    private function setCancelarPedido($id){
        $sql = "UPDATE Pedidos SET Acreditado = 6 where _idPedidos = $id";
        return $this->conn->query($sql)? true:false;
    }

    public function principal(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        switch($this->formulario->profile->tipo){
            case 'Session':
                switch($this->formulario->profile->opc){
                    case 'profile':
                        if($this->set_cliente()){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] =  "Perfil Actualizado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] =  "Error al intentar Actualizar el perfil";
                        }
                    break;
                    case 'password':
                        if($this->get_Password() === sha1($this->formulario->profile->data->passActual)){
                            if($this->set_Password()){
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] =  "Password Actualizado";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] =  "Error al intentar Actualizar el password";
                            }
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] =  "Error la contraseña actual, no coincide con la base de datos";
                        }
                        /*  */
                    break;
                    default:
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "";
                        $this->jsonData["Data"] = $this->getOneCliente($this->formulario->profile->tipo);
                    break;
                }
                
            break;
            case 'Direcciones':
            case 'Direcciones_add':
            case 'Direcciones_edit':
                switch($this->formulario->profile->opc){
                    case 'add':
                        /*Comprobamos si hay domicilios guardados si no hay predeterminado el primero*/
                        $row = $this->get_Domicilios($this->formulario->profile->data->id);
                        $this->formulario->profile->data->Predeterminado = count($row)==0? 1:0;
                        if($this->set_Domicilios($this->formulario->profile->data,$this->formulario->profile->opc)){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] =  "Domicilio Registrado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error al intentar guardar el domicilio";
                        }
                    break;
                    case 'delete':
                        if($this->set_Domicilios($this->formulario->profile->data, $this->formulario->profile->opc)){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "Domicilio Eliminado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error al intentar guardar el domicilio";
                        }
                    break;
                    case 'set':
                        $this->get_disabledDomicilioPredeterminado();
                        if($this->set_Domicilios($this->formulario->profile->data, $this->formulario->profile->opc)){
                            $this->set_DomiciliopredeterminadoCliente(
                            $this->formulario->profile->data, 
                            $this->get_OneDomicilio($this->formulario->profile->data->id_domicilio));
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "Domicilio Predeterminado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error al intentar guardar el domicilio";
                        }
                    break;
                    case 'edit':
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $this->get_OneDomicilio($this->formulario->profile->data->id_domicilio);
                        $this->jsonData["Data2"] = $this->get_Estados();
                        $this->jsonData["Data3"] = $this->get_Carrito();
                    break;
                    case 'save':
                        if($this->set_Domicilios($this->formulario->profile->data, $this->formulario->profile->opc)){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "Domicilio Guardado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error al intentar guardar el domicilio"; 
                        }
                    break;
                    default:
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "";
                        $this->jsonData["Data"] = $this->getOneCliente($this->formulario->profile->tipo);
                        $this->jsonData["Data"]["arrayDomicilios"] = $this->get_Domicilios($this->jsonData["Data"]["_id"]);
                    break;
                }
            break;
            case 'Facturacion':
            case 'Facturacion_add':
            case 'Facturacion_edit':
                    switch($this->formulario->profile->opc){
                        case 'add':
                        case 'save';
                            /* Obtenemos los datos de facturacion predeterminados */
                            $row = $this->get_dataFacturacion($this->formulario->profile->data->_id_cliente);
                            $this->formulario->profile->data->Predeterminado = count($row)==0? 1:0;
                            if($this->set_Facturacion($this->formulario->profile->opc)){
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] = "Datos de Facturacion registrado satisfactoriamente";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: al intentar registrar los datos de facturacion"; 
                            }
                            break;
                        case 'del':
                            if($this->set_Facturacion($this->formulario->profile->opc, $this->formulario->profile->data->_id)){
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] = "Datos de Facturacion han sido eliminados";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: al intentar eliminar los datos de facturacion"; 
                            }
                            break;
                        break;
                        case 'pre':
                            $this->get_datosFactuDeter();
                            if($this->set_Facturacion($this->formulario->profile->opc, $this->formulario->profile->data->_id)){
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] = "Datos de Facturacion han sido predeterminados";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: al predeterminar los datos de facturacion"; 
                            }
                            break;
                        case 'new':

                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["Data"] = $this->get_Usocfdi();
                            $this->jsonData["Data2"] = $this->get_Estados();
                        break;
                        case 'edit':
                            $this->jsonData["Bandera"] = 1;
                            
                            $this->jsonData["Data"] = array("RFC"=>$this->get_dataOneFacturacion($this->formulario->profile->data->_id),"usoCFDI"=>$this->get_Usocfdi());
                        break;
                        default:
                            $this->jsonData["Bandera"] = 1;
                             
                            $this->jsonData["Data"] = $this->get_dataFacturacion($this->formulario->profile->data->_id_cliente);
                        break;
                    }
                break;
            case 'Mispedidos':
            case 'Mispedidos_view':
                switch($this->formulario->profile->opc){
                    case 'details':
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $this->get_Mipedido($this->formulario->profile->data->_idpedido);
                    break;
                    case 'DeleteComp':
                        if($this->deleteComprobante($this->getdetalsComprobanteMipedido($this->formulario->profile->data->_idpedido))){
                            if($this->setdetalsComprobanteMipedido($this->formulario->profile->data->_idpedido)){
                                $this->jsonData["mensaje"] = "Comprobante eliminado";
                                $this->jsonData["Bandera"] = 1;
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error 404, Error al modificar el comprobante en la base de datos";
                            }  
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error 404, Error al intentar eliminar el comprobante";
                        }
                        
                    break;
                    case 'CancelPedido':
                        $data = $this->get_Mipedido($this->formulario->profile->data->_idpedido);
                        if($data["Acreditado"]==1 || $data["Acreditado"]==2){
                            //solo se agregara el monto del pedido si el pedido esta confirmado o en preparacion
                           if($this->setMonedero($data)){
                                if($this->setCancelarPedido($this->formulario->profile->data->_idpedido)){
                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "El pedido ha sido cancelado";
                                }else{
                                    $this->jsonData["Bandera"] = 0;
                                    $this->jsonData["mensaje"] = "Error: al insertar los datos al monedero"; 
                                }
                           }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: al insertar los datos al monedero";
                           }
                        }
                        break;
                    default:
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"]["No_pedidos"] = $this->get_CountMisPedidos($this->formulario->profile->data->_id);
                        $this->jsonData["Data"]["Pedidos"] = $this->get_dataMispedidos($this->formulario->profile->data->_id, $this->formulario->profile->x,$this->formulario->profile->y);
                    break;
                }
                break;
            default:
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Data"] = $this->getOneCliente($this->formulario->profile->tipo);   
            break;
        }

        print json_encode($this->jsonData);
    }
}

$app = new Profile($array_principal);
$app->principal();