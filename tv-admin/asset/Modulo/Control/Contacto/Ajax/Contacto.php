<?php

session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Contacto{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"", "Data"=>array(), "msgnuevos"=>0);
    private $formulario = array();
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    private function getContactos (){
        $array = array();
        $sql = "select * from Contacto order by fecha desc";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array,$row);
        }
       
        return $array;
    }

    private function set_leido(){
        $sql = "update Contacto set leido = 1 where _id = ".$this->formulario->contacto->id;
        return $this->conn->query($sql)? true: false;
    }

    private function getContacto(){
        $sql = "SELECT * FROM Contacto where _id=".$this->formulario->contacto->id;
        return $row = $this->conn->fetch($this->conn->query($sql));
    }

    private function getmsgnuevos(){
        $sql = "SELECT count(_id) as noleidos from Contacto where leido = 0";
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["noleidos"];
    }

    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        
        switch($this->formulario->contacto->opc){
            case "get":
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Data"] = $this->getContactos();
                $this->jsonData["msgnuevos"] = $this->getmsgnuevos();
            break;
            case 'set':
                if($this->set_leido()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getContacto();
                    $this->jsonData["msgnuevos"] = $this->getmsgnuevos();
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error: al intentar guardar en la base de datos";
                }
            break;
                    
        }
        print json_encode($this->jsonData);
    }
}

$app = new Contacto($array_principal);
$app->main();