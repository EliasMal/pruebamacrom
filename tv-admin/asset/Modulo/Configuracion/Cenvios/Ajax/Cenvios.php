<?php

    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    require_once "../../../../Clases/Funciones.php"; 
    date_default_timezone_set('America/Mexico_City');

    class Cenvios{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario;

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }

        public function main (){
            $this->formulario = json_decode(file_get_contents('php://input'));
           
            switch($this->formulario->opc){
                case 'getEstados':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Estados"] = $this->getEstados();
                    $this->jsonData["Envios"] = $this->getEnvios(); 
                break;
                case 'getMunicipios':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getMunicipios(); 
                break;
                case 'set':
                case 'edit':
                    if($this->setCenvio()){
                        $mensaje = $this->formulario->opc == "set" ? "El costo de envío se insertó satisfactoriamente" : "El costo de envío ha sido modificado";
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $mensaje; 
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error al guardar el costo de envío";
                    }
                break;
                case 'getEnvios':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Envios"] = $this->getEnvios();
                break;
                case 'off':
                    if($this->setCenvio()){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "El costo de envío ha sido desactivado"; 
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error al desactivar el costo de envío";
                    }
                break;
                default:
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getEstados(); 
                break;
            }

            print(json_encode($this->jsonData));
        }

        private function getEstados(){
            $array = array();
            $sql = "SELECT d_estado as Estado FROM CPmex GROUP BY d_estado";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array; 
        }

        private function getMunicipios(){
            $array = array();
            $estado_seguro = addslashes($this->formulario->Estado);
            $sql = "SELECT D_mnpio as Municipio, d_estado as Estado FROM CPmex WHERE d_estado LIKE '%$estado_seguro%' GROUP BY D_mnpio, d_estado";
            
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }

        private function setCenvio(){
            $accionLog = "";
            $detallesLog = "";

            $id_seguro = isset($this->formulario->id) ? intval($this->formulario->id) : 0;
            $precio_seguro = isset($this->formulario->Precio) ? floatval($this->formulario->Precio) : 0.0;
            $estado_seguro = isset($this->formulario->Estado) ? addslashes($this->formulario->Estado) : '';
            $muni_seguro = isset($this->formulario->Municipio) ? addslashes($this->formulario->Municipio) : '';
            $usr_seguro = addslashes($_SESSION["usr"]);
            $fecha_actual = date("Y-m-d");

            if($this->formulario->opc == "set"){
                $sql = "INSERT INTO Cenvios (Municipio, Estado, precio, USRCreacion, FechaCreacion, USRModificacion, FechaModificacion, Estatus) 
                        VALUES ('$muni_seguro', '$estado_seguro', '$precio_seguro', '$usr_seguro', '$fecha_actual', '$usr_seguro', '$fecha_actual', 1)";
                
                $accionLog = "CREAR_COSTO_ENVIO";
                $detallesLog = "Estado: $estado_seguro, Municipio: $muni_seguro, Costo: $ $precio_seguro";

            } else if ($this->formulario->opc == "edit"){
                $sql = "UPDATE Cenvios SET precio = '$precio_seguro', USRModificacion = '$usr_seguro', FechaModificacion = '$fecha_actual' WHERE _id = $id_seguro";
                
                $accionLog = "EDITAR_COSTO_ENVIO";
                $detallesLog = "ID Envío: $id_seguro, Nuevo Costo: $ $precio_seguro";

            } else {
                $sql = "UPDATE Cenvios SET Estatus = '0', USRModificacion = '$usr_seguro', FechaModificacion = '$fecha_actual' WHERE _id = $id_seguro";
                
                $accionLog = "DESACTIVAR_COSTO_ENVIO";
                $detallesLog = "ID Envío: $id_seguro desactivado.";
            }
            
            if($this->conn->query($sql)){
                Funciones::guardarBitacora($this->conn, 'Cenvios', $accionLog, $detallesLog);
                return true;
            } else {
                return false;
            }
        }

        private function getEnvios(){
            $array = array();
            $sql = "SELECT _id as id, Municipio, Estado, precio, Estatus FROM Cenvios WHERE Estatus = 1";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
    }
    
    $app = new Cenvios($array_principal);
    $app->main();
?>