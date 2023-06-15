<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../../Clases/dbconectar.php";
require_once "../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class getRefacciones{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $xml;
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main(){
        //$this->jsonData["data"] = $this->getRefa();
        $this->formulario = json_decode(file_get_contents('php://input'));
        if(is_null($this->formulario)){
            $this->xml = json_decode(simplexml_load_string($this->get_url_Refa()),true);
            $this->setRefa();
            $this->setBitacora();
            $this->jsonData["Bandera"] = 1;
            $this->jsonData["mensaje"] = "Los precios de las base de datos estan actualizados";
            $this->jsonData["Data"] = $this->getBitacora();
        }else{
            
            switch($this->formulario->opc){
                case 'get':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getBitacora();
                break;
                case 'set':
                    $this->xml = json_decode($this->formulario->json,true);
                    $this->setRefa();
                    $this->setBitacora();
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "La lista de precios ha sido actualizada con exito.";
                    $this->jsonData["Data"] = $this->getBitacora();
                break;
                /* default:
                    $this->xml = json_decode(simplexml_load_string($this->get_url_Refa()),true);
                    var_dump($this->xml);
                    $this->setRefa();
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Los precios de las base de datos estan actualizados";
                    $this->jsonData["Data"] = $this->getBitacora();
                break; */
            }
        }
        
        print json_encode($this->jsonData);
    }

    private function get_url_Refa(){
        $data = "";
        $defaults = array(
            CURLOPT_URL => 'https://volks.dyndns.info:444/service.asmx/datos_art',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "",
            CURLOPT_RETURNTRANSFER=>true
            );
        
        $ch = curl_init();
        //curl_setopt_array($ch,$defaults);
        curl_setopt($ch, CURLOPT_URL,'https://volks.dyndns.info:444/service.asmx/datos_art');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST,TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
        if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        
        curl_close($ch);
        return $data;
    }

    private function setRefa(){
        //$filelog = fopen("log.txt","a+") or die("Error al crear el log");
        foreach($this->xml["Table"] as $key => $value){
            $sql = "SELECT _id FROM Producto where Clave={$value["id_articulo"]}";
            $id = $this->conn->query($sql);
            if($this->conn->count_rows()!=0){
                while($row = $this->conn->fetch($id)){
                    $sql = "UPDATE Producto SET Precio1 = ".bcdiv($value["precio_5"]*1.16,'1',2)." WHERE _id = {$row["_id"]}";
                    $this->conn->query($sql);
                    //fwrite($filelog, $value["id_articulo"]. " - ". $sql . " Se Actualizo\n");
                }
            }else{
                //fwrite($filelog, $value["id_articulo"]. " - ". $sql  . " No se Actualizo\n");
            }
        }
        //fclose($filelog);
        return;
    }

    private function setBitacora(){
        $usr = is_null($this->formulario)? "root":$this->formulario->usr;
        $sql = "INSERT INTO logActualizacion (mensaje, usr, fecha, hora) values 
        ('Actualizacion de precios','$usr','". date("Y-m-d") ."','". date("H:i:s"). "')";
        return $this->conn->query($sql)? true:false;
    }

    private function getBitacora(){
        $array = array();
        $sql = "SELECT * FROM logActualizacion order by fecha desc";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            array_push($array,$row);
        }
        return $array;

    }
}

$app = new getRefacciones($array_principal);
$app->main();
