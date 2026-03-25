<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";

    class Bitacora {
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function principal(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            switch ($this->formulario->opc) {
                case 'get_logs':
                    $sql = "SELECT * FROM Bitacora_Auditoria ORDER BY fecha DESC LIMIT 500";
                    $resultado = $this->conn->query($sql);
                    
                    $logs = array();
                    while($row = $this->conn->fetch($resultado)){
                        array_push($logs, $row);
                    }
                    
                    if(count($logs) > 0){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $logs;
                    } else {
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["Data"] = [];
                    }
                    break;
            }
            print json_encode($this->jsonData);
        }
    }
    
    $app = new Bitacora($array_principal);
    $app->principal();
?>