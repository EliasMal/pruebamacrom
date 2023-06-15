<?php

require_once 'tv-admin/asset/Clases/ConexionMySQL.php';
require_once 'tv-admin/asset/Clases/dbconectar.php';
 
class Model{
    private $conn;
    function __construct($array=array()){
        $this->conn = new HelperMySql($array["server"],$array["user"],$array["pass"],$array["db"]);
    }
    
    function __destruct() {
        unset($this->conn);
    }

    public function getallPedidos(){
        $sql = "update Pedidos set Acreditado = 1 where _idPedidos = {$_SESSION["id_pedido"]}";
        return $this->conn->query($sql);
    }
}