<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";
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
                        $this->jsonData["Data"] = $this->getPost($this->formulario->id, $this->formulario->skip, $this->formulario->limit);
                    break;
                }    
    
                print json_encode($this->jsonData);
        }

        private function getEntradas($skip, $limit){
            $array = array();
            $skip_s = intval($skip);
            $limit_s = intval($limit);
            
            $sql = "SELECT _id, Title, Contenido, imagendestacada, Estatus, Publicar, Fecha FROM Blog WHERE Publicar = 1 AND Estatus = 1 ORDER BY Fecha DESC LIMIT $skip_s, $limit_s";
            $res = $this->conn->query($sql);
            
            while($row = $this->conn->fetch($res)){
                $row["Title"] = html_entity_decode($row["Title"]);
                $row["Contenido"] = substr(strip_tags(html_entity_decode($row["Contenido"])), 0, 150);
                $row["Estatus"] = $row["Estatus"] == 1 ? true : false;
                $row["Publicar"] = $row["Publicar"] == 1 ? true : false;
                array_push($array, $row);
            }
            return $array;
        }

        private function getOneEntrada($id){
            $id_s = intval($id);
            
            $sql = "SELECT _id, Title, Contenido, Estatus, Publicar, Imagen, DATE(Fecha) as FechaCorta FROM Blog WHERE _id = $id_s";
            $row = $this->conn->fetch($this->conn->query($sql));
            
            if($row) {
                $row["Title"] = html_entity_decode($row["Title"]);
                $row["Contenido"] = html_entity_decode($row["Contenido"]);
                $row["Estatus"] = $row["Estatus"] == 1 ? true : false;
                $row["Publicar"] = $row["Publicar"] == 1 ? true : false;
            }
            return $row;
        }

        private function getPost($id, $skip=0, $limit=3){
            $array = array();
            $id_s = intval($id);
            $skip_s = intval($skip);
            $limit_s = intval($limit);

            $sql = "SELECT _id, Title, Contenido, imagendestacada, Estatus, Publicar, Fecha FROM Blog WHERE Publicar = 1 AND Estatus = 1 AND _id != $id_s ORDER BY Fecha DESC LIMIT $skip_s, $limit_s";
            $res = $this->conn->query($sql);
            
            while($row = $this->conn->fetch($res)){
                $row["Title"] = html_entity_decode($row["Title"]);
                $row["Contenido"] = substr(strip_tags(html_entity_decode($row["Contenido"])), 0, 150);
                $row["Estatus"] = $row["Estatus"] == 1 ? true : false;
                $row["Publicar"] = $row["Publicar"] == 1 ? true : false;
                array_push($array, $row);
            }
            return $array;
        }
    }

    $app = new Blog($array_principal);
    $app->main();
?>