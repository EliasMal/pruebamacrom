<?php
session_name("loginCliente");
session_start();
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";

date_default_timezone_set('America/Mexico_City');

class uploadfile{
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $comprobante;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main(){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->comprobante =  isset($_FILES)? $_FILES:array();
        switch($this->formulario["opc"]){
            case 'upload':
                if($this->uploadComprobante()){
                    $this->setPerfil($this->comprobante["file"]["name"], $this->formulario["_idpedido"]);
                    $this->jsonData["Bandera"]=1;
                    $this->jsonData["mensaje"]="El archivo se envio satisfactoriamente";
                    $this->jsonData["Data"] = array("comprobante"=>$this->comprobante["file"]["name"]);
                }else{
                    $this->jsonData["Bandera"]=0;
                    $this->jsonData["mensaje"]="Error: no se pudo enviar el archivo";
                }
                break;
        }
        print json_encode($this->jsonData);
    }

    public function uploadComprobante(){
        if($this->comprobante["file"]["name"]!="" and $this->comprobante["file"]["size"]!=0){
            $subdir ="../../../"; 
            $dir = "Public/Comprobantes/";
            $archivo = $this->comprobante["file"]["name"];
            if(!is_dir($subdir.$dir)){
                mkdir($subdir.$dir,0755);
            }
            if($archivo && move_uploaded_file($this->comprobante["file"]["tmp_name"], $subdir.$dir.$archivo)){
                //$this->rutaimagen= $dir.$archivo;
                return true;
            }else{
                echo "no se subio la imagen". $subdir.$dir.$archivo;
            }
        }else{
            return false;
        }
    }

    public function setPerfil($file, $_idpedido){
        $sql = "UPDATE Pedidos SET comprobante='{$file}' where _idPedidos=$_idpedido";
        return $this->conn->query($sql)? true: false;
    }


}

$app = new uploadfile($array_principal);
$app->main();