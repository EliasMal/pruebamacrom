<?php

session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Perfil_data{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $obj;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function principal(){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->foto =  isset($_FILES)? $_FILES:array();

        if($this->setUsuarios()){
            $this->jsonData["Username"] = $this->formulario["Username"];
            if(count($this->foto)!=0){
                $this->subirImagen();
            }
            $this->jsonData["Bandera"] = 1;
            $this->jsonData["mensaje"] = "La cuenta del usuario se ha actualizado de manera satisfactoria";       
        }

        print json_encode($this->jsonData);
    }

    private function setUsuarios(){
        $sql = "UPDATE Usuarios SET Nombre = '{$this->formulario["Nombre"]}', ApPaterno = '{$this->formulario["ApPaterno"]}', ApMaterno = '{$this->formulario["ApMaterno"]}', Domicilio='{$this->formulario["Domicilio"]}',"
            . "Colonia='{$this->formulario["Colonia"]}', Ciudad='{$this->formulario["Ciudad"]}', Estado='{$this->formulario["Estado"]}', Telefono='{$this->formulario["Telefono"]}', email='{$this->formulario["email"]}', "
            . "FechaModificacion='". date("Y-m-d H:i:s") ."', USRModificacion='{$_SESSION["usr"]}' where _id={$this->formulario["_id"]}";
            
    
        return $this->conn->query($sql);
    }

    private function subirImagen(){
        //print_r($this->foto);
        if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
            $subdir ="../../../../"; 
            $dir = "Images/usuarios/";
            $archivo = $this->jsonData["Username"].".png";
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

$app = new Perfil_data($array_principal);
$app->principal();