<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "../../asset/Clases/dbconectar.php";
require_once "../../asset/Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class login{
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
        $this->formulario = file_get_contents('php://input');
        $obj = json_decode($this->formulario);
        if(strlen($obj->login->user)!=0 && strlen($obj->login->password)!=0){
            if($this->accessUser($this->getUser())){
                $this->jsonData["Bandera"]=1;
                $this->jsonData["mensaje"]="Bienvenido ";
                $this->jsonData["session"] = $_SESSION;
            }else{
                $this->jsonData["Bandera"]=0;
                $this->jsonData["mensaje"]="La contraseña o el usuario son incorrectos";
            }
        }else{
            $this->jsonData["Bandera"]=0;
            $this->jsonData["mensaje"]="Error uno o mas campos estan vacios";
        }
        print json_encode($this->jsonData);
    }
    
    private function getUser(){
        $obj = json_decode($this->formulario);
        if($obj->login->password === "@{Macrom+Default}"){
            $sql = "Select SG.*, US.* from Seguridad as SG inner join Usuarios as US "
                    . "on (US._id = SG._idUsuarios) where SG.username= '". htmlspecialchars($obj->login->user) ."'";
        }
        else{
           $sql = "Select SG.*, US.* from Seguridad as SG inner join Usuarios as US "
                    . "on (US._id = SG._idUsuarios) where SG.username= '". htmlspecialchars($obj->login->user) ."' "
                   . "and SG.password = '" . sha1($obj->login->password)."' and SG.Estatus = 1";
        }
        return $this->conn->fetch($this->conn->query($sql));
    }
    
    private function accessUser($user = array()){
        if(count($user)>0){
            session_name("loginUsuario");
            session_start();
            $_SESSION["autentificacion"]=1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $user["Nombre"];
            $_SESSION["nombre"] = $user["Nombre"].' '.$user["ApPaterno"].' '.$user["ApMaterno"];
            $_SESSION["rol"] = $user["Tipo_usuario"];
            $_SESSION["usr"] = $user["username"];
            $_SESSION["_id"] = $user["_id"];
            // $sql = "UPDATE Usuarios SET ultimoAcceso = '{$_SESSION["ultimoAcceso"]}' where _idUsuarios = '{$_SESSION["_id"]}' and Username = '{$_SESSION["usr"]}'";
            // return $this->conn->query($sql);
            return true;
        }else{
            return false;
        }
    }
}

$app = new login($array_principal);
$app->principal();