<?php

session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Monedero{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"", "Data"=>array());
    private $formulario = array();

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main(){
        $this->methodo = $_SERVER['REQUEST_METHOD'];
        
        //var_dump($this->methodo, $this->formulario);    
        switch($this->methodo){
            case 'GET':
                $opc = isset($_GET["opc"])? $_GET["opc"]:"";
                $idCliente = isset($_GET["idCliente"])? $_GET["idCliente"]:"";
                
                $skip = isset($_GET["page"])? $_GET["page"]:0;
                $limit = isset($_GET["limit"])? $_GET["limit"]:15; 
                switch($opc){
                    case "Detalles";
                        $this->jsonData["Bandera"]=1;
                        $this->jsonData["Data"]["Cliente"] = $this->getCliente($idCliente);
                        $this->jsonData["Data"]["History"] = $this->getMonederoxcliente($idCliente);
                        $this->jsonData["Data"]["NoMonedero"] = $this->getNoMonederoxcliente($idCliente);
                        $this->jsonData["Data"]["Monedero"] = $this->getTotalMonederoxcliente($idCliente);
                        
                    break;
                    case "history":
                        $this->jsonData["Bandera"]=1;
                        $this->jsonData["Data"]["NoMonedero"] = $this->getNoMonederoxcliente($idCliente);
                        $this->jsonData["Data"]["History"] = $this->getMonederoxcliente($idCliente,$skip,$limit);
                        //$this->jsonData["Data"]["Monedero"] = $this->getTotalMonederoxcliente($idCliente);
                        break;
                    case "Monedero":
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"]["Monedero"] =  $this->getTotalMonederoxcliente($idCliente);
                        break;
                    default:
                        $this->jsonData["Data"]["Monedero"] = $this->getMonedero();
                        $this->jsonData["Data"]["NoRegistros"] = $this->getNolistadoMonedero();
                        $this->jsonData["Data"]["Registros"] = $this->getlistadoMonedero($skip);
                        $this->jsonData["Bandera"]=1;
                    break;
                }
                
            break;
            case 'POST':

                break;
        }
        print json_encode($this->jsonData);
    }

    private function getMonedero(){
        $sql = "SELECT if(isnull(sum(Importe)), 0, sum(Importe) ) as total from Monedero";
        $row = $this->conn->fetch($this->conn->query($sql));
        return floatval($row["total"]);
    }

    private function getNolistadoMonedero(){
        $sql = "select concat(C.nombres,' ', C.Apellidos) as nombreCliente, sum(M.Importe) from Monedero as M 
        inner join clientes as C on (M._id_cliente = C._id)
        group by _id_cliente";
        $this->conn->query($sql);
        return $this->conn->count_rows();
    }

    private function getlistadoMonedero($skip=0, $limit=15){
        $sql = "select SG.username, concat(C.nombres,' ', C.Apellidos) as nombreCliente, sum(M.Importe) as Importe, M._id_cliente from Monedero as M 
        inner join clientes as C on (M._id_cliente = C._id)
        inner join Cseguridad as SG on (SG._id_cliente=C._id) 
        group by M._id_cliente, SG.username LIMIT $skip, $limit";
        $this->conn->query($sql);
        return $this->conn->fetch_all();
    }

    private function getCliente($idCliente){
        $sql = "SELECT * from clientes where _id=$idCliente";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function getNoMonederoxcliente($idCliente){
        $sql = "SELECT count(*) as Reg from Monedero where _id_cliente = $idCliente";
        $row = $this->conn->fetch($this->conn->query($sql));
        return intval($row["Reg"]);
    }

    private function getMonederoxcliente($idCliente, $skip=0, $limit=15){
        $sql = "SELECT * from Monedero where _id_cliente = $idCliente order by fecha_created desc LIMIT $skip, $limit";
        $this->conn->query($sql);
        return $this->conn->fetch_all();
    }

    private function getTotalMonederoxcliente($idCliente){
        $sql = "SELECT if(isnull(sum(Importe)), 0, sum(Importe) ) as total from Monedero where _id_cliente=$idCliente";
        $row = $this->conn->fetch($this->conn->query($sql));
        return floatval($row["total"]);
    }
}

$app = new Monedero($array_principal);
$app->main();