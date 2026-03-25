<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class repRefacciones{
        private $conn;
        private $jsonData = array("Bandera"=>0, "Mensaje"=>"");
        private $formulario;
        
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            $json = json_decode(file_get_contents('php://input'), true);
            $this->formulario = (object) ($json['mantenimiento'] ?? $json);

            $opc = $this->formulario->opc ?? '';

            switch ($opc) {
                case "activarUS":
                    if($this->actUS()){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Mensaje"] = "El sistema ha regresado a la normalidad. Usuarios desbloqueados.";
                    } else {
                        $this->jsonData["Mensaje"] = "Error al intentar activar a los usuarios.";
                    }
                    break;

                case "desactivarUS":
                    if($this->bloqUS()){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Mensaje"] = "Modo Mantenimiento ACTIVADO. Usuarios bloqueados correctamente.";
                    } else {
                        $this->jsonData["Mensaje"] = "Error al intentar bloquear a los usuarios.";
                    }
                    break;
                    
                case "newCreated":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["data"] = $this->getNewCreated();
                    $this->jsonData["Mensaje"] = $this->formulario->dateNew ?? '';
                    break;
                    
                case "ticketPromedio":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["data"] = $this->getTicketPromedio();
                    break;
                    
                case "topProductos":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["data"] = $this->getTopProductos();
                    break;

                case "getEstadoMantenimiento":
                    $sql = "SELECT modo_mantenimiento FROM Configuracion_Sistema WHERE id = 1 LIMIT 1";
                    $res = $this->conn->query($sql);
                    $estado = 0;
                    
                    if($res){
                        $row = $this->conn->fetch($res);
                        $estado = intval($row['modo_mantenimiento']);
                    }
                    
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["estado"] = $estado;
                    break;
            }
           
            header('Content-Type: application/json');
            print json_encode($this->jsonData);
        }


        private function getTopProductos(){
            $dateOld = $this->formulario->dateOld ?? date("Y-m-d", strtotime("-30 days"));
            $dateNew = $this->formulario->dateNew ?? date("Y-m-d");

            $sql = "SELECT P._id, P.Clave, P.Producto AS NombreProducto, P.Modelo, 
                SUM(DP.cantidad) AS UnidadesVendidas, 
                SUM(DP.Importe * DP.cantidad) AS IngresoTotal
                FROM DetallesPedidos DP
                INNER JOIN Producto P ON DP._idProducto = P._id
                INNER JOIN Pedidos PED ON DP._idPedidos = PED._idPedidos
                WHERE PED.Fecha >= '{$dateOld} 00:00:00' AND PED.Fecha <= '{$dateNew} 23:59:59'
                AND PED.Acreditado IN (1, 2, 3, 4, 5)
                GROUP BY P._id, P.Clave, P.Producto, P.Modelo
                ORDER BY UnidadesVendidas DESC
                LIMIT 15";

            $array = array();
            $res = $this->conn->query($sql);
            if($res){
                while($row = $this->conn->fetch($res)){
                    $row['UnidadesVendidas'] = (int)$row['UnidadesVendidas'];
                    $row['IngresoTotal'] = (float)$row['IngresoTotal'];
                    array_push($array, $row);
                }
            }
            return $array;
        }

        private function getNewCreated(){
            $array = array();
            $dateOld = $this->formulario->dateOld ?? '';
            $dateNew = $this->formulario->dateNew ?? '';
            $sql="SELECT * From Producto where dateCreated BETWEEN '$dateOld 00:00:00' and '$dateNew 23:59:59' order by dateCreated desc";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){
                array_push($array, $row);
            }
            return $array;
        }

        private function getTicketPromedio(){
            $dateOld = $this->formulario->dateOld ?? date("Y-m-d", strtotime("-30 days"));
            $dateNew = $this->formulario->dateNew ?? date("Y-m-d");
            
            $sqlResumen = "SELECT COUNT(_idPedidos) as totalPedidos, COALESCE(SUM(Importe + cenvio), 0) as ingresoTotal, COALESCE(AVG(Importe + cenvio), 0) as ticketPromedio 
            FROM Pedidos WHERE Fecha >= '{$dateOld} 00:00:00' AND Fecha <= '{$dateNew} 23:59:59' AND Acreditado IN (1, 2, 3, 4, 5)";
            
            $resumen = array("totalPedidos" => 0, "ingresoTotal" => 0, "ticketPromedio" => 0);
            $resResumen = $this->conn->query($sqlResumen);
            if($resResumen){
                $resumenBD = $this->conn->fetch($resResumen);
                if($resumenBD) $resumen = $resumenBD;
            }

            $sqlGrafica = "SELECT DATE(Fecha) as fecha, COUNT(_idPedidos) as pedidos_dia, COALESCE(SUM(Importe + cenvio), 0) as total_vendido, COALESCE(AVG(Importe + cenvio), 0) as ticket_diario 
            FROM Pedidos WHERE Fecha >= '{$dateOld} 00:00:00' AND Fecha <= '{$dateNew} 23:59:59' AND Acreditado IN (1, 2, 3, 4, 5) GROUP BY DATE(Fecha) ORDER BY fecha ASC";

            $grafica = array();
            $resGrafica = $this->conn->query($sqlGrafica);
            if($resGrafica){
                while($row = $this->conn->fetch($resGrafica)){
                    array_push($grafica, $row);
                }
            }

            $sqlDetalles = "SELECT P._idPedidos as Folio, P.Fecha, P.FormaPago, P.Servicio as Paqueteria, (P.Importe + P.cenvio) as TotalVenta, IFNULL(CONCAT(C.nombres, ' ', C.Apellidos), 'Cliente Desconocido') as Cliente 
            FROM Pedidos P LEFT JOIN clientes C ON P._idCliente = C._id WHERE P.Fecha >= '{$dateOld} 00:00:00' AND P.Fecha <= '{$dateNew} 23:59:59' AND P.Acreditado IN (1, 2, 3, 4, 5) ORDER BY P.Fecha DESC";
                            
            $detalles = array();
            try {
                $resDetalles = $this->conn->query($sqlDetalles);
                if($resDetalles){
                    while($row = $this->conn->fetch($resDetalles)){
                        array_push($detalles, $row);
                    }
                }
            } catch (Exception $e) {}
            
            return array("resumen" => $resumen, "grafica" => $grafica, "detalles" => $detalles);
        }

        private function bloqUS(){
            try {
                $fecha = date("Y-m-d H:i:s");
                $sql = "UPDATE Configuracion_Sistema SET modo_mantenimiento = 1, fecha_actualizacion = '$fecha' WHERE id = 1";
                return $this->conn->query($sql);
            } catch (Exception $e) {
                return false; 
            }
        }

        private function actUS(){
            try {
                $fecha = date("Y-m-d H:i:s");
                $sql = "UPDATE Configuracion_Sistema SET modo_mantenimiento = 0, fecha_actualizacion = '$fecha' WHERE id = 1";
                return $this->conn->query($sql);
            } catch (Exception $e) {
                return false;
            }
        }
    }

    $app = new repRefacciones($array_principal);
    $app->main();
?>