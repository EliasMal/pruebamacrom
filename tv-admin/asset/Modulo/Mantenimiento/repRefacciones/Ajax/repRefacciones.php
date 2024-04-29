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

        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));

            switch ($this->formulario->matenimiento->opc) {
                case "activarUS":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Mensaje"] = "Usuarios Desbloqueados";
                    $this->actUS();
                break;
                case "desactivarUS":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Mensaje"] = "Usuarios Bloqueados";
                    $this->bloqUS();
                break;
            }
            // $this->jsonData["Data"] = $this->setRefacciones($this->getRefacciones());
           
            print json_encode($this->jsonData);
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

        private function bloqUS(){
            $sql ="UPDATE Seguridad SET Estatus = 0 where Tipo_usuario != 'root'";
            return $this->conn->query($sql);
        }

        private function actUS(){
            $sql ="UPDATE Seguridad SET Estatus = 1";
            return $this->conn->query($sql);
        }

    }

    $app = new repRefacciones($array_principal);
    $app->main();