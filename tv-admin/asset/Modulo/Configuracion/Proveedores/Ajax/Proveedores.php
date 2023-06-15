<?php

session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

Class Proveedores{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $fecha;
    private $url;
    
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->fecha = date("Y-m-d H:i:s");
        $this->url = preg_replace("#(admin\.)?#i","", $_SERVER["HTTP_ORIGIN"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function principal(){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->foto =  isset($_FILES)? $_FILES:array();
        switch ($this->formulario["opc"]) {
            case 'buscar':
                $this->jsonData["data"] = $this->getProveedores();
                $this->jsonData["Bandera"] = 1;
            break;
            case 'edit':
            case 'new':
            case 'enabled':
            case 'disabled';
                if($this->setProveedores()){
                    $this->formulario["lastid"] = $this->formulario["opc"]=="edit"? $this->formulario["_id"]:$this->conn->last_id();
                    if(count($this->foto)!=0){
                        $this->subirImagen();
                    }
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = $this->getMensajeSuccess();
                }else{
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = $this->getMensajeError();
                    $this->jsonData["Error"] = $this->conn->error;
                }

            break;

        }
        $this->jsonData["dominio"] = $this->url;
        print json_encode($this->jsonData);
    }

    private function setProveedores(){
        
        switch($this->formulario["opc"]){
            case 'new':
                $sql = "INSERT INTO Proveedor (Proveedor, Estatus, tag_title, tag_alt, USRCreacion, USRModificacion, FechaCreacion, fechaModificacion) values 
                ('{$this->formulario["Proveedor"]}',1,'{$this->formulario["tag_title"]}','{$this->formulario["tag_alt"]}', '{$_SESSION["usr"]}','{$_SESSION["usr"]}','{$this->fecha}','{$this->fecha}')";
            break;
            case 'edit':
                $sql = "UPDATE Proveedor set Proveedor = '{$this->formulario["Proveedor"]}', 
                USRModificacion='{$_SESSION["usr"]}', fechaModificacion='{$this->fecha}', 
                tag_title = '{$this->formulario["tag_title"]}', tag_alt = '{$this->formulario["tag_alt"]}' 
                WHERE _id='{$this->formulario["_id"]}'";
            break;
            case 'enabled':
                $sql = "UPDATE Proveedor set Estatus = 1 where _id = {$this->formulario["_id"]}";
            break;
            case 'disabled':
                $sql = "UPDATE Proveedor set Estatus = 0 where _id = {$this->formulario["_id"]}";
            break;
        }
        
        return $this->conn->query($sql)? true: false;
    }

    private function getProveedores(){
        $array = array();
        $sql = "SELECT * FROM Proveedor where Estatus = ".$this->formulario["historico"];
        $id = $this->conn->query($sql);
        while($row= $this->conn->fetch($id)){
            $row["foto"] = file_exists("../../../../../../images/Marcasrefacciones/".$row["_id"].".png");
            array_push($array, $row);
        }
        return $array;
    }

    private function getMensajeSuccess(){
        $mensaje = "";
             switch($this->formulario["opc"]){
                case 'new':
                    $mensaje = "El proveedor ha sido Creado";
                    break;
                case 'edit':
                    $mensaje = "El proveedor ha sido Modificado";
                    break;
                case 'disabled':
                    $mensaje = "El proveedor ha sido Desactivado";
                    break;
                case 'enabled':
                    $mensaje = "El proveedor ha sido Activado";
                    break;
             }
        return $mensaje;
    }

    private function getMensajeError(){
        $mensaje = "";
             switch($this->formulario["opc"]){
                case 'new':
                    $mensaje = "Error: El proveedor no ha sido Creado";
                    break;
                case 'edit':
                    $mensaje = "Error: El proveedor no ha sido Modificado";
                    break;
                case 'disabled':
                    $mensaje = "Error: El proveedor no ha sido Desactivado";
                    break;
                case 'enabled':
                    $mensaje = "Error: El proveedor no ha sido Activado";
                    break;
             }
        return $mensaje;
    }

    private function subirImagen(){
        //print_r($this->foto);
        if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
            $subdir ="../../../../../../"; 
            $dir = "images/Marcasrefacciones/";
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

$app = new Proveedores($array_principal);
$app->principal();