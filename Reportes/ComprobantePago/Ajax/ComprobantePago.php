<?php
    session_name("loginCliente");
    session_start();
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

    class ComprobantePago{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"", "Data"=>"");
        private $formulario;

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            if(!isset($this->formulario->ficha->id) || empty($this->formulario->ficha->id)){
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["mensaje"] = "ID de pedido no proporcionado.";
                print json_encode($this->jsonData);
                return;
            }

            $idPedidoSeguro = intval($this->formulario->ficha->id);
            $datosPedido = $this->get_Mipedido($idPedidoSeguro);

            if($datosPedido){
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Data"] = $datosPedido;
            } else {
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["mensaje"] = "No se encontró el pedido o no pertenece al usuario.";
            }

            print json_encode($this->jsonData);
        }

        private function get_Mipedido($_idpedido){
            $sql = "SELECT P.*, (P.Importe + P.cenvio) as Totalpedido, concat(C.nombres, ' ', C.Apellidos ) as nombreCliente, Cd.*, 
            F.Rfc, F.Razonsocial, F.Domicilio as Fdomicilio, F.Codigo_postal as FCodigo_postal, F.Colonia as Fcolonia, F.Ciudad as FCiudad,
            F.Estado as FEstado 
            from Pedidos as P 
            inner join clientes as C on (C._id = P._idCliente) 
            left join Cdirecciones as Cd on (Cd._id = P._id_cdirecciones)
            left join Facturacion as F on (F._id = P._id_facturacion)
            where P._idPedidos = $_idpedido"; // Variable ya saneada
            
            $res = $this->conn->query($sql);

            if($res && $this->conn->count_rows($res) > 0){
                $row = $this->conn->fetch($res);
                $row["Detalles"] = $this->get_DetallesMispedidos($_idpedido);
                return $row;
            }
            return false;
        }

        private function get_DetallesMispedidos($_idPedido, $limit = false){
            $array = array();
            $sql = "select DP.* , P.Producto, P.Clave, P.No_parte from DetallesPedidos as DP 
                    inner join Producto as P on (DP._idProducto = P._id)
                    where DP._idPedidos = $_idPedido ";
            $sql .= $limit? " LIMIT 0, 2":"";        
            
            $id = $this->conn->query($sql);
            
            if($id){
                while($row = $this->conn->fetch($id)){
                    $rutaWebp = "../../../images/refacciones/{$row["Clave"]}.webp";
                    $rutaPng = "../../../images/refacciones/{$row["Clave"]}.png";
                    
                    $row["imagen"] = file_exists($rutaWebp) || file_exists($rutaPng);
                    array_push($array, $row);
                }
            }
            return $array;
        }
    }

    $app = new ComprobantePago($array_principal);
    $app->main();