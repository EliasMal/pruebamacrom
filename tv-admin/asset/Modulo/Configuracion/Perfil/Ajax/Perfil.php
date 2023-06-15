<?php

session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Perfil{
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
        
        $this->formulario = file_get_contents('php://input');
        $this->obj = json_decode($this->formulario);
        switch ($this->obj->usuarios->opc) {
            case 'get':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Data"] = $this->getUsuario();
                $this->jsonData["img"] = file_exists("../../../../Images/usuarios/". $this->jsonData["Data"]["Username"].".png");
                break;
            case 'pass':
                if($this->setPassword()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "El password ha sido cambiado";
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error al intentar cambiar el password";
                }
                break;
        }
        print json_encode($this->jsonData);
    }

    private function getUsuario(){
        $sql = "select US.*, S._id as id_seguridad, S.Tipo_usuario from Usuarios as US" 
        . " inner join Seguridad as S on (US._id = S._idUsuarios) where US.username='{$_SESSION["usr"]}'";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function setPassword(){
        $sql = "UPDATE Seguridad SET password=SHA('".$this->obj->usuarios->pass."') where _id=".$this->obj->usuarios->id;
        return $this->conn->query($sql);
    }
}


$app = new Perfil($array_principal);
$app->principal();