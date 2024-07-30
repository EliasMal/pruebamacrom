<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Refacciones
 *
 * @author francisco
 */



session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');


class Galeria{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function principal(){
        
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->foto =  isset($_FILES)? $_FILES:array();
        switch ($this->formulario["opc"]) {
            case "new":
            case "edit":
                $id = $this->setGaleria();

                if($id){
                    $this->formulario["lastid"] = $this->formulario["opc"]=="edit"? $this->formulario["_id"]:$this->conn->last_id();
                    if(count($this->foto)!=0){
                        $this->subirImagen();
                        $this->Setimgactividad();
                        $this->LastMod();
                    }
                    $this->jsonData["mensaje"] = "La imagen se agrego a la galeria";
                    $this->jsonData["Bandera"] = 1;
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = $id;
                }
                
            break;
            case 'get':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Data"] = $this->getGaleria();
            break;
            case 'erase':
                if($this->eraseImagen()){
                    if($this->setGaleria()){
                        $this->LastMod();
                        $this->Delimgactividad();
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mansaje"] = "La Imagen seleccionada ha sido eliminada de la galeria";
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mansaje"] = "Error: no se elimino el registro de la base de datos de la galeria";
                    }
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error: La imagen soleccionada no se pudo eliminar de la galeria";
                }
                
            break;
        }
        print json_encode($this->jsonData);
    }

    private function getGaleria (){
        $array = array();
        $sql = "SELECT _id, tag_alt, tag_title FROM galeriarefacciones where id_producto = {$this->formulario["id"]}";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            $row["imagen"] =  file_exists("../../../../../../images/galeria/{$row["_id"]}.png");
            array_push($array, $row);
        }
        return $array;
    }

    private function setGaleria(){
        switch($this->formulario[opc]){
            case 'new':
                $fecha = date("Y-m-d");
                $sql = "INSERT INTO galeriarefacciones (tag_alt, tag_title, id_producto, USRCreacion, USRModificacion, FechaCreacion, FechaModificacion) " 
               . "values ('{$this->formulario["tag_alt"]}','{$this->formulario["tag_title"]}','{$this->formulario["id_refaccion"]}','{$_SESSION["usr"]}','{$_SESSION["usr"]}','$fecha','$fecha')";
            break;
            case 'erase':
                $sql = "DELETE FROM galeriarefacciones where _id=".$this->formulario["id"]; 
            break;
        }
        return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
    }

    private function Setimgactividad(){
        $sql = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('{$this->formulario["id_refaccion"]}', '{$_SESSION["nombre"]}', 'Agrego nueva imagen a galeria.', '".date("Y-m-d H:i:s")."');";
        return $this->conn->query($sql);
    }

    private function Delimgactividad(){
        $sql = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('{$this->formulario["id_refaccion"]}', '{$_SESSION["nombre"]}', '&quot;Elimino la imagen:&quot;{$this->formulario["id"]},&quot;de la galeria&quot;', '".date("Y-m-d H:i:s")."');";
        return $this->conn->query($sql);
    }
    private function LastMod(){
        $sql = "UPDATE Producto SET userModify='{$_SESSION["nombre"]}',dateModify='".date("Y-m-d H:i:s")."' WHERE _id = {$this->formulario["id_refaccion"]}";
        return $this->conn->query($sql);
    }
    
    private function subirImagen(){
        //print_r($this->foto);
        if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
            $subdir ="../../../../../../"; 
            $dir = "images/galeria/";
            $archivo = $this->formulario["lastid"].".webp";
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

    private function EraseImagen(){
                
        if(file_exists("../../../../../../images/galeria/{$this->formulario["id"]}.webp")){
            return unlink("../../../../../../images/galeria/{$this->formulario["id"]}.webp");
        }else{
            return false;
        }
    }
}

$app = new Galeria($array_principal);
$app->principal();