<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class WebPrincipal{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $fecha;
    private $url;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->fecha = date("Y-m-d");
        $this->url = preg_replace("#(admin\.)?#i","", $_SERVER["HTTP_ORIGIN"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main(){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->foto =  isset($_FILES)? $_FILES:array();
        

        switch ($this->formulario["opc"]) {
            case 'get':
                $this->jsonData["Data"] = $this->getImagen();
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["categoria"] = $this->formulario["Categoria"];
            break;
            case 'set':
                if(count($this->foto) !=0){
                    if($this->subirImagen()){
                        if($this->setImagen()){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "La imagen se guardo satisfactoriamente";
                            $this->jsonData["categoria"] = $this->formulario["Categoria"];
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error: al intentar guardar la imagen en la base de datos"; 
                        }
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error: Al intentar subir la imagen al servidor";
                    }
                } 
            break;
            case 'off':
                if($this->setImagen()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Imagen Eliminada";
                    $this->jsonData["categoria"] = $this->formulario["Categoria"];
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error al intentar eliminar la Imagen";
                }
            break;
        }
        $this->jsonData["dominio"]=$this->url;
        print json_encode($this->jsonData);
    }


    private function setImagen(){
        switch($this->formulario["opc"]){
            case 'set':
                $sql = "INSERT INTO Imagenes(imagen,Categoria,Estatus, FechaCreacion, FechaModificacion, Diseño) values(
                   '{$this->foto["file"]["name"]}','{$this->formulario["Categoria"]}','{$this->formulario["Estatus"]}','$this->fecha','$this->fecha',
                   '{$this->formulario["Disenio"]}')";
            break;
            case 'off':
                $sql = "DELETE FROM Imagenes Where _id='{$this->formulario["_id"]}'";
                $this->BorrarImagen();
            break;
        }
        return $this->conn->query($sql)? true: false;
    }

    private function getImagen(){
        $array = array("Escritorio"=>array(), "Movil"=>array());
        $limit = "";
        $diseño = array("Escritorio","Movil");
        $categoria = array("Principal","Catalogos","Compras","Nosotros","Contacto","Session");
        if(count(array_keys($categoria,$this->formulario["Categoria"]))!=0){
            $limit = "limit 1";
        }
        foreach($diseño as $key => $value){
            $sql = "Select * from Imagenes where Categoria='{$this->formulario["Categoria"]}' and Estatus = {$this->formulario["Estatus"]} and Diseño='$value' $limit";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                array_push($array[$value], $row);
            }
        }

        
        return $array;
    }

    private function subirImagen(){
        
        if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
            $subdir ="../../../../../../"; 
            $dir = "images/Banners/";
            $archivo = $this->foto["file"]["name"];
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

    private function BorrarImagen(){
        $sql = "SELECT imagen FROM Imagenes Where _id='{$this->formulario["_id"]}'";
        $result = $this->conn->query($sql);
        $imagen = $result->fetch_array()[0];
        if(file_exists("../../../../../../images/Banners/{$imagen}")){
            return unlink("../../../../../../images/Banners/{$imagen}");
        }else{
            return false;
        }
    }
}

$app = new WebPrincipal($array_principal);
$app->main();