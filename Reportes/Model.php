<?php

require_once "../tv-admin/asset/Clases/dbconectar.php";
require_once "../tv-admin/asset/Clases/ConexionMySQL.php";

class Model{
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

    public function getImportecompra ($id){
        $sql = "SELECT * FROM Pedidos where _idPedidos = $id";
        return $this->conn->fetch($this->conn->query($sql));
    }
}