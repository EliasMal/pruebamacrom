<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Login{
    private $conn;
    private $formulario = array();
    private $jsonData = array("mensaje"=>"", "Bandera" => 0);
    private $dataLogin = array();
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        switch($this->formulario->Login->opc){
            case 'in':
                if($this->setSession($this->getUser())){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mansaje"] = "Bienvenido {$this->dataLogin["nombres"]}";
                    $this->jsonData["session"] = $_SESSION;
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error: el usuario no existe";
                }
                break;
            case 'out':
                if($this->outSession()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mansaje"] = "La session se cerro";
                    
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mansaje"] = "Error: No se pudo eliminar la session";
                }
                break;
        }
        
        print json_encode($this->jsonData);
    }
    
    private function getUser(){
        $sql = "SELECT C.nombres, C.apellidos, C.Codigo_postal, CS._id_cliente, CS.cuponacre FROM Cseguridad as CS inner join clientes as C on (CS._id_cliente = C._id) "
                . "where username='{$this->formulario->Login->user}' and password='". sha1($this->formulario->Login->password)."'";
        $this->dataLogin = $this->conn->fetch($this->conn->query($sql));
        
        return count($this->dataLogin)!=0? true:false;
        
    }
    
    private function setSession($flag = false){
        if($flag){
            session_name("loginCliente");
            session_start();
            $_SESSION["autentificacion"]=1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $this->dataLogin["nombres"];
            $_SESSION["nombre"] = $this->dataLogin["nombres"].' '.$this->dataLogin["apellidos"];
            $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
            $_SESSION["Cenvio"] = $this->getCenvio();
            $_SESSION["usr"] = $this->formulario->Login->user;
            $_SESSION["cupon"] = "macrupon";
            $_SESSION["acreditacion"] = $this->dataLogin["cuponacre"];
            return true;
        }else{
            return false;
        }
    }
    
    private function getCenvio(){
        $array = array("Envio"=>"","costo"=>0, "Servicio"=>"") ;
        $sql = "select CE.precio from Cenvios as CE 
        inner join CPmex as CP on (CP.D_mnpio = CE.Municipio)
        where CP.d_codigo = '{$this->dataLogin["Codigo_postal"]}' group by CE.precio";
        $id = $this->conn->query($sql);
        if($this->conn->count_rows() != 0){
            $row = $this->conn->fetch();
            $array["Envio"] = "L"; //Envio Local
            $array["costo"] = floatval($row["precio"]);
            $array["Servicio"] = "METROPOLITANO";
        }else{
            $array["Envio"] = "N"; //Envio nacional
            $array["costo"] = 0;
        }
        return $array;
    }

    private function outSession(){
        session_name("loginCliente");
        session_start();
        session_destroy();
        return true;
    }
}

$app = new Login($array_principal);
$app->main();