<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');
    
    class Categorias {
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }
        
        public function principal() {
            $this->formulario = array_map("htmlspecialchars", $_POST);
            $this->foto =  isset($_FILES)? $_FILES:array();
            
            switch ($this->formulario["opc"]) {
                case 'buscar':
                    $this->jsonData["data"] = $this->getCategorias();
                    $this->jsonData["Bandera"] = 1;
                        
                    
                    break;
                case 'edit':
                case 'new':
                case 'disabled':
                case 'enabled':
                    if($this->setCategorias()){
                        $this->formulario["lastid"] = $this->formulario["opc"]=="edit"?$this->formulario["_id"]:$this->conn->last_id();
                        
                        if(count($this->foto)!=0){
                            $this->subirImagen();
                        }
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $this->getMensajeSuccess();
                    }else{
                        $this->jsonData["mensaje"] = $this->getMensajeError();
                    }
                    break;
                
            }
            print json_encode($this->jsonData);
        }
        
        
        private function getMensajeSuccess(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "La Marca ha sido Creada";
                        break;
                    case 'edit':
                        $mensaje = "La Marca ha sido Modificada";
                        break;
                    case 'disabled':
                        $mensaje = "La Marca ha sido Desactivada";
                        break;
                    case 'enabled':
                        $mensaje = "La Marca ha sido Activada";
                        break;
                 }
            return $mensaje;
        }
        
        private function getMensajeError(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "Error: La Marca no ha sido Creada";
                        break;
                    case 'edit':
                        $mensaje = "Error: La Marca no ha sido Modificada";
                        break;
                    case 'disabled':
                        $mensaje = "Error: La Marca no ha sido Desactivada";
                        break;
                    case 'enabled':
                        $mensaje = "Error: La Marca no ha sido Activada";
                        break;
                 }
            return $mensaje;
        }
        
        private function getCategorias(){
            $array = array();
            $sql = "SELECT * FROM Categorias where Status = ".$this->formulario["historico"];
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                $row["foto"] = file_exists("../../../../../../images/Categorias/".$row["_id"].".png");
                array_push($array, $row);
            }
            return $array;
        }
        
        private function setCategorias (){
            if($this->formulario["opc"]=="new"){
                $sql = "INSERT INTO Categorias (Categoria, Status, USRCreacion,USREdicion, FechaCreacion, FechaModificacion ) values "
                        . "('{$this->formulario["Categoria"]}','1','{$_SESSION["usr"]}','{$_SESSION["usr"]}','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
            }else if($this->formulario["opc"]=="edit"){
                $sql = "UPDATE Categorias SET Categoria='{$this->formulario["Categoria"]}', USREdicion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' where _id= ".$this->formulario["_id"];
            }else if($this->formulario["opc"]=="disabled"){
                $sql = "UPDATE Categorias SET Status=0, USREdicion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' where _id= ".$this->formulario["_id"];
            }else if($this->formulario["opc"]=="enabled"){
                $sql = "UPDATE Categorias SET Status=1, USREdicion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' where _id= ".$this->formulario["_id"];
            }
        return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
        }
        
        
        private function subirImagen(){
            //print_r($this->foto);
            if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
                $subdir ="../../../../../../"; 
                $dir = "images/Categorias/";
                $archivo = $this->formulario["lastid"].".png";
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir,0755);
                }
                if($archivo && move_uploaded_file($this->foto["file"]["tmp_name"], $subdir.$dir.$archivo)){
                    //$this->rutaimagen= $dir.$archivo;
                    return true;
                }else{
                    echo "no se subio la imagen";
                }
            }else{
                return false;
            }
        }
    }
    
    $app = new Categorias($array_principal);
    $app->principal();
