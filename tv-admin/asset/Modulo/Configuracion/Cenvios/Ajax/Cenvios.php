<?php

    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class Cenvios{
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
                        $mensaje = $this->formulario->opc == "set" ? "El Costo de envio se inserto satisfactoriamente": "El costo de envio ha sido modificado";
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $mensaje; 
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error: al insertar el nuevo costo";
                    }
                    
                break;
                case 'getEnvios':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Envios"] = $this->getEnvios();
                break;
                case 'off':
                    if($this->setCenvio()){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "El Costo de envio ha sido desactivado"; 
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error: al desactivar el costo de envio";
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
            $sql = "select d_estado as Estado from CPmex group by d_estado";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array; 

        }

        private function getMunicipios(){
            $array = array();
            $sql = "select D_mnpio as Municipio, d_estado as Estado from CPmex where d_estado like 
            '%{$this->formulario->Estado}%' group by D_mnpio, D_estado";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }

        private function setCenvio(){
            if($this->formulario->opc == "set"){
                $sql = "INSERT INTO Cenvios (Municipio, Estado, precio, USRCreacion, FechaCreacion, USRModificacion, FechaModificacion, Estatus) 
                values ('{$this->formulario->Municipio}','{$this->formulario->Estado}','{$this->formulario->Precio}',"
                . "'{$_SESSION["usr"]}','". date("Y-m-d") . "','{$_SESSION["usr"]}','" . date("Y-m-d") . "',1)";
            }else if ($this->formulario->opc == "edit"){
                $sql = "UPDATE Cenvios SET precio = '{$this->formulario->Precio}'  where _id = {$this->formulario->id}";
            }else{
                $sql = "UPDATE Cenvios SET Estatus = '0' where _id = {$this->formulario->id}";
            }
            
            return $this->conn->query($sql)? true: false;
        }

        private function getEnvios(){
            $array = array();
            $sql = "select _id as id, Municipio, Estado, precio, Estatus from Cenvios where Estatus = 1";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
    }
    
    $app = new Cenvios($array_principal);
    $app->main();