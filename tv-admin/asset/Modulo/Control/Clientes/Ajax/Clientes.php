<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Clientes
 *
 * @author francisco
 */
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Clientes {
    //put your code here
    
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
         
        switch ($this->formulario->cliente->opc){
            case 'get':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getClientes();
                break;
            case 'new':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getnewClientes($this->getFirstWeekDay());
            break;
            case 'set':
                $this->setClientes($this->getIdCseguridad());
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getClientes();
                break;
            case 'pass':
                $pass = $this->create_password();
                $this->setPass($pass, $this->getIdCseguridad());
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["pass"] = $pass;
                break;
            case 'perfil':
                $this->jsonData["Bandera"] = 1;
                $element = $this->getOneCliente();
                $element["avisoprivacidad"] = $element["avisoprivacidad"]==0? false:true;
                $this->jsonData["data"] = $element;
                break;
        }
        print json_encode($this->jsonData);
    }
    
    private function getClientes(){
        $array = array();
        $historico = $this->formulario->cliente->historico =="true"? 0:1;
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
        C.correo, C.telefono, Cs.estatus  from clientes as C 
        inner join Cseguridad as Cs on (Cs._id_cliente = C._id) where Cs.estatus = $historico order by C.Apellidos, C.nombres";
        
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }
    
    private function getnewClientes($week_start){
        $array = array();
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
        C.correo, C.telefono, Cs.estatus  from clientes as C 
        inner join Cseguridad as Cs on (Cs._id_cliente = C._id) where C.FechaCreacion >= '$week_start' 
        order by C.Apellidos, C.nombres";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function getFirstWeekDay(){
        if(date("D")=="Mon"){
            $week_start = date("Y-m-d");
        }else{
            $week_start = date("Y-m-d", strtotime("last Monday", time()));
        }
        return $week_start;
    }

    private function getOneCliente(){
        $sql = "Select * from clientes as C inner join Cseguridad as Cs on (Cs._id_cliente = C._id)
         where C._id = ". $this->formulario->cliente->id;
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function getIdCseguridad(){
        $sql = "select _id from Cseguridad where _id_cliente = ".$this->formulario->cliente->id;
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["_id"];
    }

    private function setClientes($idCseguridad){
        $sql = "update Cseguridad set Estatus = {$this->formulario->cliente->estatus} where _id = {$idCseguridad}";
        $this->conn->query($sql);
        $sql = "update clientes set Estatus = {$this->formulario->cliente->estatus} where _id= {$this->formulario->cliente->id}";
        $this->conn->query($sql);
        return true;
    }
    
    private function setPass ($pass, $id){
        $sql = "update Cseguridad set password = SHA('$pass') where _id = $id";
        return $this->conn->query($sql);    
    }

    private function create_password(){
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $longitudCadena=strlen($cadena);
        $pass = "";
        $longitudPass=10;

        for($i=1 ; $i<=$longitudPass ; $i++){
            $pos=rand(0,$longitudCadena-1);
            $pass .= substr($cadena,$pos,1);
        }
        return $pass;
    }
}

$app = new Clientes($array_principal);
$app->main();