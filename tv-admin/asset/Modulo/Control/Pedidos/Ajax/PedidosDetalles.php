<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class PedidosDetalles{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main (){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->archivos =  isset($_FILES)? $_FILES:array();
        switch ($this->formulario["opc"]) {
            case 'save':
                /**Reviso si estamos cancelando el pedido */
                if($this->formulario["Acreditado"]==6){
                    $data = $this->getPedidosDetalles($this->formulario["_idPedidos"]);
                    if($data["Acreditado"]==1 || $data["Acreditado"]==2){
                         //solo se agregara el monto del pedido si el pedido esta confirmado o en preparacion
                        $this->setMonedero($data);
                    }
                }
                $id = $this->setPedidosDetalles($this->formulario["Acreditado"], $this->formulario["GuiaEnvio"], $this->formulario["_idPedidos"]);
                if($id){
                    if(count($this->archivos)!=0){
                        $this->setPedidoDetallesfiles($this->uploadfiles($this->archivos),$this->formulario["_idPedidos"]);
                    }
                    if($this->formulario["Acreditado"] == 5){
                        //Eliminamos el comprobante de pago cuando este ya este entregado
                        $arrayTemp = $this->getdetalsComprobanteMipedido($this->formulario["_idPedidos"]);
                        if($this->removeFile("../../../../../../Public/Comprobantes/".$arrayTemp["comprobante"])){
                            $this->setdetalsComprobanteMipedido($this->formulario["_idPedidos"]);
                        }
                    }else if($this->formulario["Acreditado"] == 6){

                    }
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Los datos se han almacenado de manera satisfactoria";
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error al guardar los datos";
                }
                break;
            case 'deletefile':
                
                if($this->removeFile("../../../../../../Public/Facturas/".$this->formulario["file"])){
                    $this->setDeletefilePedido($this->formulario["_idPedido"], $this->formulario["tipo"]);
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Archivo Eliminado";
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "El archivo no existe en el servidor";
                }
                break;
            case 'deleteArtic':
                if($id = $this->getOneDetallePedido($this->formulario["idDetalle"])){
                    if($this->setMonederoArticulo($id)){
                        if($this->setOneDetallePedido($this->formulario["idDetalle"])){
                            $this->setImportePedidosDetallesxArticulo($id["_idPedidos"], ($id["Importe"]*$id["cantidad"]));
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "Articulo eliminado";
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error al eliminar el articulo";
                        }
                        $this->jsonData["Data"] = $id;
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error al insertar el articulo en el monedero";  
                    }
                }else{  
                    
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error al bucsar el articulo";
                }
                    
                break;
        }
        print json_encode($this->jsonData);
    }

    private function getdetalsComprobanteMipedido($id){
        $sql = "SELECT comprobante  from Pedidos where _idPedidos=$id";
        return $this->conn->fetch($this->conn->query($sql));
    }
    private function setdetalsComprobanteMipedido($id){
        $sql ="UPDATE Pedidos SET comprobante='' where _idPedidos = $id";
        return $this->conn->query($sql)? true:false;
    }


    private function removeFile($file){
        return unlink($file);
    }

    private function uploadfiles($files){
        $array = array();
        foreach($files as $key => $value){
            $subdir ="../../../../../../"; 
            $dir = "Public/Facturas/";
            $archivo = $value["name"];
            if(!is_dir($subdir.$dir)){
                mkdir($subdir.$dir,0755);
            }
            if($archivo && move_uploaded_file($value["tmp_name"], $subdir.$dir.$archivo)){
                array_push($array,$archivo);
            }else{
                echo "Error al subir el archivo $archivo";
            }
            
        }
        return $array;
    }

    private function setDeletefilePedido($id, $tipo){
       $sql = "UPDATE Pedidos SET $tipo = '' where _idPedidos = $id ";
       return $this->conn->query($sql);
    }

    private function getPedidosDetalles($id){
        $sql = "SELECT * FROM Pedidos where _idPedidos = $id";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function setPedidosDetalles($acreditado, $guiaenvio, $_id){
        $sql = "UPDATE Pedidos SET Acreditado='$acreditado', GuiaEnvio='$guiaenvio' WHERE _idPedidos = $_id";
        return $this->conn->query($sql)? true: false;
    }

    private function setImportePedidosDetallesxArticulo($idPedido, $importe){
        $sql = "UPDATE Pedidos SET Importe = (Importe - $importe) where _idPedidos = $idPedido";
        return $this->conn->query($sql)? true: false;
    }

    private function getOneDetallePedido($idDetalle){
        $sql = "select DP.*, P._idCliente, P._idPedidos from DetallesPedidos as DP 
                inner join Pedidos as P on (DP._idPedidos = P._idPedidos)
                where DP._id = $idDetalle ";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function setOneDetallePedido($idDetalle){
        $sql = "UPDATE DetallesPedidos SET Estatus = 0, FechaEditar='". date("Y-m-d H:i:s")."' where _id=$idDetalle";
        return $this->conn->query($sql)? true: false;
    }

    private function setPedidoDetallesfiles($array, $_id){
        $campos = "";
        foreach($array as $key => $value){
            if($key == 0){
                $campos = "archivoxml='$value',";
            }else if($key == 1){
                $campos .= "archivopdf='$value' ";
            }
        }
        $sql = "UPDATE Pedidos SET $campos where _idPedidos = $_id";
        return $this->conn->query($sql)? true: false;
    }

    private function setMonedero($data){
        $sql = "INSERT INTO Monedero(_id_cliente, Descripcion, Importe, movimiento) 
        values({$data["_idCliente"]},'Deposito monedero cancelacion pedido No. {$data["noPedido"]}', ". ($data["Importe"]+$data["cenvio"]) .", 1)";
        return $this->conn->query($sql);
    }

    private function setMonederoArticulo($data){
        $sql = "INSERT INTO Monedero(_id_cliente, Descripcion, Importe, movimiento) 
            values ({$data["_idCliente"]},'Deposito monedero cancalacion articulo codigo: {$data["_idProducto"]}',". 
            ($data["cantidad"]*$data["Importe"]) .", 1)";
        return $this->conn->query($sql);
    }
}

$app = new PedidosDetalles($array_principal);
$app->main();
