<?php
    session_name("loginCliente");
    session_start();
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

    class Blog{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario;
        

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));
                switch($this->formulario->opc){
                    case 'get':
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $this->getEntradas($this->formulario->skip, $this->formulario->limit);
                    break;
                    case 'getOne':
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $this->getOneEntrada($this->formulario->id);
                    break;
                    case 'getPost':
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $this->getPost($this->formulario->id,$this->formulario->skip, $this->formulario->limit);
                        break;
                }    
    
                print json_encode($this->jsonData);
        }

        private function getEntradas($skip, $limit){
            $array = array();
            $sql = "SELECT * from Blog where Publicar = 1 and Estatus = 1 order by Fecha desc LIMIT $skip, $limit";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $row["Title"] = html_entity_decode($row["Title"]);
                $row["Contenido"] = substr(strip_tags(html_entity_decode($row["Contenido"])),0,150);
                $row["Estatus"]=$row["Estatus"]==1? true:false;
                $row["Publicar"]=$row["Publicar"]==1? true:false;
                array_push($array, $row);
            }
            return $array;
        }

        private function getOneEntrada($id){
            $sql = "SELECT _id, Title, Contenido, Estatus, Publicar, Imagen, DATE(Fecha) as FechaCorta FROM Blog WHERE _id = $id";
            $row = $this->conn->fetch($this->conn->query($sql));
            $row["Title"] = html_entity_decode($row["Title"]);
            $row["Contenido"] = html_entity_decode($row["Contenido"]);
            $row["Estatus"]=$row["Estatus"]==1? true:false;
            $row["Publicar"]=$row["Publicar"]==1? true:false;

            return $row;
        }

        private function getPost($id, $skip=0, $limit=3){
            $array = array();
            $sql = "SELECT * from Blog where Publicar = 1 and Estatus = 1 and  _id != $id order by Fecha desc LIMIT $skip, $limit";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $row["Title"] = html_entity_decode($row["Title"]);
                $row["Contenido"] = substr(strip_tags(html_entity_decode($row["Contenido"])),0,150);
                $row["Estatus"]=$row["Estatus"]==1? true:false;
                $row["Publicar"]=$row["Publicar"]==1? true:false;
                array_push($array, $row);
            }
            return $array;
        }
        
    }

    $app = new blog($array_principal);
    $app->main();