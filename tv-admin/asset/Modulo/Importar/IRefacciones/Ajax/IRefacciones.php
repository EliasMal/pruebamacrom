<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class IRefacciones{
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
            $this->formulario = array_map("htmlspecialchars", $_POST);
            $this->archivoCSV =  isset($_FILES)? $_FILES:array();

            $this->jsonData["Bandera"] = 1;
            $this->jsonData["Mensaje"] = "Se insertaron " .  $this->readArchivo() . "Registros";
           
            print json_encode($this->jsonData);
        }

        private function readArchivo(){
            $linea = 0;
            $header;
            $query="";
            $archivo = fopen($this->archivoCSV["file"]["tmp_name"],"r");
            while(($datos = fgetcsv($archivo,","))== true){
                $linea++;
                if($linea == 1){
                    $header = $datos;
                }else{
                    $sql = "UPDATE Producto SET ";
                    for($i = 1; $i < count($header); $i++){
                        $query.= $header[$i]."='".$datos[$i]."',";
                    }
                    $query = trim($query,",");
                    $sql .= $query. " WHERE _id={$datos[0]}";
                    
                    if(!$this->conn->query($sql)){
                        
                       var_dump($linea, $sql, $this->conn->error);
                    } 
                    $query="";
                }
            }
            fclose($archivo);
            
            return $linea-1;
        }
    }

    $app = new IRefacciones($array_principal);
    $app->main();