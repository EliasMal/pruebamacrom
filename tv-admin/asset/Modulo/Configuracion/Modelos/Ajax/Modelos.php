<?php

session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');
    
    class Modelos {
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();
        private $url;

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->url = preg_replace("#(admin\.)?#i","", $_SERVER["HTTP_ORIGIN"]);
        }

        public function __destruct() {
            unset($this->conn);
        }
        
        public function principal() {
            $this->formulario = array_map("htmlspecialchars", $_POST);
            $this->foto =  isset($_FILES)? $_FILES:array();
            
            switch ($this->formulario["opc"]) {
                case 'buscar':
                    switch($this->formulario["tipo"]){
                        case 'Modelos':
                            $this->jsonData["Data"]["NoModelos"] = $this->getNoModelos($this->formulario["find"]);
                            $this->jsonData["Data"]["Modelos"] = $this->getModelos($this->formulario["find"], $this->formulario["skip"], $this->formulario["limit"]);
                            $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Marcas':
                            $this->jsonData["data"] = $this->getMarcas();
                            $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Anios':
                            $this->jsonData["data"] = $this->getAnios();
                            $this->jsonData["Bandera"] = 1;
                            break;
                    }          
                    break;
                case 'edit':
                case 'new':
                case 'disabled':
                case 'enabled':
                    if($this->setModelos()){
                        $this->formulario["lastid"] = $this->conn->last_id();
                        
                        if(count($this->foto)!=0){
                            $this->subirImagen();
                        }
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $this->getMensajeSuccess();
                    }else{
                        $this->jsonData["mensaje"] = $this->getMensajeError();
                    }
                    break;
                case 'newanios':
                case 'editanio':
                case "deleteanio":
                    if($this->setAnios()){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $this->getMensajeSuccessAnios();
                    }else{
                        $this->jsonData["mensaje"] = $this->getMensajeErrorAnios();
                    }
                    
                    break;
                
            }
            $this->jsonData["dominio"]=$this->url;
            print json_encode($this->jsonData);
        }
        
        private function getMensajeSuccess(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "El Vehiculo ha sido Creado";
                        break;
                    case 'edit':
                        $mensaje = "El Vehiculo ha sido Modificado";
                        break;
                    case 'disabled':
                        $mensaje = "El Vehiculo ha sido Desactivado";
                        break;
                    case 'enabled':
                        $mensaje = "El Vehiculo ha sido Activado";
                        break;
                 }
            return $mensaje;
        }
        
        private function getMensajeSuccessAnios(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'newanios':
                        $mensaje = "El Modelo ha sido Creado";
                        break;
                    case 'editanio':
                        $mensaje = "El Modelo ha sido Modificado";
                        break;
                    case 'deleteanio':
                        $mensaje = "El Modelo ha sido Eliminado";
                        break;
                    
                 }
            return $mensaje;
        }
        
        private function getMensajeError(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "Error: El Vehiculo no ha sido Creado";
                        break;
                    case 'edit':
                        $mensaje = "Error: El Vehiculo no ha sido Modificado";
                        break;
                    case 'disabled':
                        $mensaje = "Error: El Vehiculo no ha sido Desactivado";
                        break;
                    case 'enabled':
                        $mensaje = "Error: La Vehiculo no ha sido Activado";
                        break;
                 }
            return $mensaje;
        }
        
        private function getMensajeErrorAnios(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'newanios':
                        $mensaje = "Error: El Modelo no ha sido Creado";
                        break;
                    case 'editanio':
                        $mensaje = "Error: El Modelo no ha sido Modificado";
                        break;
                    case 'deleteanio':
                        $mensaje = "Error: El Modelo no ha sido Eliminado";
                        break;
                    
                 }
            return $mensaje;
        }
        

        private function getNoModelos($find=""){
            $array = array();
            $sql = "SELECT count(*) as total FROM Modelos as M inner join Marcas as MA on(M._idMarca = MA._id)"
                    ." WHERE M.Estatus = {$this->formulario["historico"]} and M.Modelo like '%$find%'";
            $row = $this->conn->fetch($this->conn->query($sql));            
            return $row["total"];
        }

        
        private function getModelos($find="", $skip=0, $limit=10){
            $array = array();
            $sql = "SELECT M.*, MA.Marca FROM Modelos as M inner join Marcas as MA on(M._idMarca = MA._id) "
                    ."where M.Estatus = {$this->formulario["historico"]} and M.Modelo like '%$find%' Limit $skip, $limit";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                $row["foto"] = file_exists("../../../../../../images/Marcas/".$row["_idMarca"].".png");
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getMarcas(){
            $array = array();
            $sql = "SELECT * FROM Marcas where Estatus = ".$this->formulario["historico"];
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                $row["foto"] = file_exists("../../../../../../images/Marcas/".$row["_id"].".png");
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getAnios(){
            $array = array();
            $sql = "SELECT * FROM Anios where _idModelo = ".$this->formulario["_idModelo"];
            $id= $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
        
        private function setModelos (){
            $sql = "";
            switch($this->formulario["opc"]){
                case 'new':
                $sql = "INSERT INTO Modelos(Modelo, Estatus, _idMarca, USRCreacion,USRModificacion, FechaCreacion, FechaModificacion) VALUES "
                        . "('{$this->formulario["Modelo"]}','1','{$this->formulario["_idMarca"]}',"
                        ."'{$_SESSION["usr"]}','{$_SESSION["usr"]}','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
                        break;
            case 'edit':
                $sql = "UPDATE Modelos SET Modelo='{$this->formulario["Modelo"]}', _idMarca='{$this->formulario["_idMarca"]}', USRModificacion='{$_SESSION["usr"]}', FechaModificacion='"
                       .date("Y-m-d H:i:s")."', Primer_anio_fabricacion = {$this->formulario["Primer_anio_fabricacion"]}, Ultimo_anio_fabricacion = {$this->formulario["Ultimo_anio_fabricacion"]} where _id= ".$this->formulario["_id"];
                       break;
            case 'disabled':
                $sql = "UPDATE Modelos SET Estatus=0, USRModificacion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' where _id= ".$this->formulario["_id"];
                break;
            case 'enabled':
                $sql = "UPDATE Modelos SET Estatus=1, USRModificacion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' where _id= ".$this->formulario["_id"];
            break;
        }
        return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
        }
        
        private function setAnios(){
            switch ($this->formulario["opc"]){
                case 'newanios':
                    $sql = "INSERT INTO Anios (Anio,  _idModelo, USRCreacion,USREdicion, FechaCreacion, FechaModificacion ) values "
                        . "('{$this->formulario["Anio"]}','{$this->formulario["_idModelo"]}','{$_SESSION["usr"]}','{$_SESSION["usr"]}','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
                    break;
                case 'editanio':
                   $sql = "UPDATE Anios SET Anio = '{$this->formulario["Anio"]}', USREdicion='{$_SESSION["usr"]}', FechaModificacion='".
                        date("Y-m-d H:i:s")."' where _id=". $this->formulario["_id"];
                    break;
                case 'deleteanio':
                    $sql = "DELETE FROM Anios where _id=". $this->formulario["_id"];
                    break;
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
    
    $app = new Modelos($array_principal);
    $app->principal();

