<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class repRefacciones{
        private $conn;
        private $jsonData = array("Bandera"=>0,"Mensaje"=>"");
        private $formulario = array();
        private $archivoCSV;
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }

        private function getRefacciones(){
            $sql="SELECT group_concat(_id) as id from Producto where Publicar=0";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["id"];
        }

        private function setRefacciones($string=""){
           $string = trim($string,','); 
           $sql ="UPDATE Producto SET Publicar = 1, userCreated = 'Admin', userModify = 'Admin'
            where _id in ( $string ) ";
            return $this->conn->query($sql);
        }

        public function main(){
            
            $this->jsonData["Bandera"] = 1;
            $this->jsonData["Mensaje"] = "Registros Actualizados";
            $this->jsonData["Data"] = $this->setRefacciones($this->getRefacciones());
           
            print json_encode($this->jsonData);
        }
    }

    $app = new repRefacciones($array_principal);
    $app->main();