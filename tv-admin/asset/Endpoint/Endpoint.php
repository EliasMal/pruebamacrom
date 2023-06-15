<?php
include("../Clases/ConexionMySQL.php");
include("../Clases/AESCrypto.php");
include("../Clases/dbconectar.php");



Class Endpoint{
    //private $key = "5dcc67393750523cd165f17e1efadd21"; //semilla para pruebas
    private $key = "DA6DAC61810C99C5017DE457560134C6";
    private $AES;
    private $respuesta;
    private $xml;


    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->AES = new AESCrypto();
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main (){
        if($_POST){
    
            /* Genero un log para saber si la recepcion es correcta del Endpoint */
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "--------------------------\n");
            fwrite($filelog, $_REQUEST["strResponse"]);
            fwrite($filelog, "Respuesta en claro--------\n");
            $this->respuesta = $this->AES->desencriptar($_REQUEST["strResponse"],$this->key);
            $this->xml = simplexml_load_string($this->respuesta);
            fwrite($filelog, $this->respuesta);
            fwrite($filelog, "--------------------------\n");
            fwrite($filelog, $this->xml->response);
            //$this->xml->reference = "29"; // ese valor solo sera para el desarrollo se tiene que eliminar cuando este en produccion
            $id = $this->getPedidoId();
            if($this->xml->response == "approved"){
                fwrite($filelog, "Obtengo el id del pedido-----------------\n");
                fwrite($filelog, $id);
                $this->setPedido( $id, 1);
            }else{
                $this->setPedido($id,6);
            }
            fwrite($filelog, $this->setLogTerminal($id) . "Obtengo el id del pedido-----------------\n");
            fclose($filelog);

        }else{
            $filelog = fopen("log.txt","a+") or die("Error al crear el log");
            fwrite($filelog, "no se recibio ningun post");
            fclose($filelog);
        }
    } 
    
    private function getPedidoId(){
        $sql = "Select _idPedidos from Pedidos where noPedido = {$this->xml->reference}";
        $res = $this->conn->fetch($this->conn->query($sql));
        return $res["_idPedidos"];
    }

    private function setPedido($id, $acreditado){

        $sql = "UPDATE Pedidos SET Acreditado = $acreditado where _idPedidos = $id";
        return $this->conn->query($sql)? true:false;
    }
    
    private function setLogTerminal($id){
        $array = explode("/",$this->xml->date);
        $fecha = $this->xml->date != ""? "{$array[2]}-{$array[1]}-{$array[0]}":date("Y-m-d");
        $sql = "INSERT INTO LogTerminal(_idPedidos, Reference, Responce, folioCpagos, auth, cd_response, time, date, nb_company, cc_type, cc_number, cc_mask)
                value($id, '{$this->xml->reference}','{$this->xml->response}','{$this->xml->foliocpagos}','{$this->xml->auth}','{$this->xml->cd_response}',
                '{$this->xml->time}','{$fecha}','{$this->xml->nb_company}','{$this->xml->cc_type}','{$this->xml->cc_number}','{$this->xml->cc_mask}')";
        return $this->conn->query($sql)? "Datos Ingresados":"Error en la insercion $sql";
    }

}

$app = new Endpoint($array_principal);
$app->main();
