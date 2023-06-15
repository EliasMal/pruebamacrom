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
            $this->jsonData["Bandera"] = 1;
            $this->jsonData["Data"] = $this->get_Mipedido($this->formulario->ficha->id);
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
            where _idPedidos = $_idpedido";
            $row = $this->conn->fetch($this->conn->query($sql));
            $row["Detalles"] = $this->get_DetallesMispedidos($_idpedido);
            return $row;
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
    }

    $app = new ComprobantePago($array_principal);
    $app->main();
