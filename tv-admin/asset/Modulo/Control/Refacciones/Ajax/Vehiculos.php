<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class Vehiculos{
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
            
        }

        public function setVehiculo(){
            $sql =  "INSERT INTO compatibilidad (clave, idmarca, idmodelo, generacion, ainicial, afinal, motor, transmision, especificaciones, id_imagen) value "
            ."('{$this->formulario["clave"]}', '{$this->formulario["idmarca"]}', '{$this->formulario["idmodelo"]}', '{$this->formulario["generacion"]}', '{$this->formulario["ainicial"]}', '{$this->formulario["afinal"]}',"
            ."'{$this->formulario["motor"]}','{$this->formulario["transmision"]}','{$this->formulario["especificaciones"]}','{$this->formulario["id_imagen"]}')";
            return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
        }


    }

    $app = new Vehiculos($array_principal);
    $app->main();