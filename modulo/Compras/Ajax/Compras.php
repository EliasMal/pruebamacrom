<?php
    session_name("loginCliente");
    session_start();
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
    require_once "../../../tv-admin/asset/Clases/redpack.php";
    
    class Compras{
        private $conn;
        private $redpack;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario;
        #private $pin = "QA hQG/YPy/+TcEsrkOxXlIUxWycU4NAeuce1btGHkms4Q=";
        private $pin = "PROD hQG/YPy/+TcEsrkOxXlIUxWycU4NAeuce1btGHkms4Q=";
        private $idUsuario = 1436;

        public function __construct($array) {
            $this->redpack = new redpack();
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }
        
        public function __destruct() {
            unset($this->conn);
            unset($this->redpack);
        }

        private function getOneDatauser($username){
            $sql = "select C._id as id, C.nombres, C.Apellidos, C.correo from Cseguridad as CS 
                    inner join clientes as C on (C._id = CS._id_cliente) 
                    where CS.username = '$username'";
            return $this->conn->fetch($this->conn->query($sql));
        }

        private function dataDireccion($id_user){
            $array = array("Bandera"=> false, "data"=>array());
            $sql = "SELECT * FROM Cdirecciones where _id_cliente = $id_user and Predeterminado = 1";
            $id = $this->conn->query($sql);
            if($this->conn->count_rows() > 0){
                $array["Bandera"] = true;
                $array["data"] = $this->conn->fetch($id);
            }
            return $array;
        }

        private function dataFacturacion($id_user){
            $array = array("Bandera"=> false, "data"=>array());
            $sql = "SELECT F.*, U.Descripción as usocfdi from Facturacion as F inner join usocfdi as U on (U._id = F.cfdi) 
                    where _id_cliente = $id_user and Predeterminado = 1";
            $id = $this->conn->query($sql);
            if($this->conn->count_rows() > 0){
                $array["Bandera"] = true;
                $array["data"] = $this->conn->fetch($id);
            }
            return $array;
        }

        private function getCostoEnvioLocal($cp){
            $sql = "select CE.precio from Cenvios as CE 
            inner join CPmex as CP on (CP.D_mnpio = CE.Municipio)
            where CP.d_codigo = $cp and CE.Estatus = 1 group by CE.precio";
            return $this->conn->fetch($this->conn->query($sql));
        }

        private function getCenvio($cp = null){
            $Cenvio =  array("Envio"=>"","Costo"=>0, "Servicio"=>"", "paqueteria"=>"", "enviodias"=>0);
            $temp = $this->getCostoEnvioLocal($cp);
            //var_dump($temp);
            if(count($temp) > 0){
                $Cenvio["Envio"] = "L";
                $Cenvio["Costo"] = floatval($temp["precio"]);
                $Cenvio["Servicio"] = "METROPOLITANO";
            }else{
                $Cenvio["Envio"] = "N";
                $Cenvio["Servicio"] = "NACIONAL" ; 
                $Cenvio["Costos"] = $this->getCotizarEnvio($cp);  
            }
            return $Cenvio;
        }

        private function getDataUser($username){
            $dataArray = array("datauser"=>array(), "datafacturacion"=>array());
            $dataArray["datauser"] = $this->getOneDatauser($username);
            $dataArray["datadomicilio"] = $this->dataDireccion($dataArray["datauser"]["id"]);
            $dataArray["datafacturacion"] = $this->dataFacturacion($dataArray["datauser"]["id"]);
            $dataArray["Cenvio"] = is_null($dataArray["datadomicilio"])? array():$this->getCenvio((int)$dataArray["datadomicilio"]["data"]["Codigo_postal"]);
            return $dataArray;
        }

        public function main (){
            $this->formulario = json_decode(file_get_contents('php://input'));
            switch($this->formulario->Compras->opc){
                case 'get':
                
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getDataUser($this->formulario->Compras->username);
                break;

                case 'cotizar':
                    default:
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getCotizarEnvio();
                break;
            }    

            print json_encode($this->jsonData);
        }

        private function getDestinatario(){
            $array = array("codigoPostal"=>0);
            $sql = "select CL.Codigo_postal from Cseguridad as CS inner join clientes as CL on (CS._id_cliente = CL._id)
                    where username='{$_SESSION["usr"]}'";
            $row = $this->conn->fetch($this->conn->query($sql));
            $array["codigoPostal"]=(int)$row["Codigo_postal"];
            return $array;
        }   
        private function get_OneDomicilio($id){
            $sql = "SELECT * from Cdirecciones where _id = $id";
            return $this->conn->fetch($this->conn->query($sql));
        }

        private function getPaquetes(){
            $array = array();
            $i = 1;
            /*PESO VOLUMETICOS 
                (ANCHO * LARGO * ALTO) / 5000
            */
            foreach($_SESSION["cart"] as $key => $value){
                $paquete = array(
                    "largo" => (int)$value["largo"],
                    "ancho" => (int)$value["cantidad"]*(int)$value["ancho"],
                    "alto" => (int)$value["alto"],
                    "peso" => $value["cantidad"]*$value["peso"],
                    "consecutivo" => $i
                );
                array_push($array, $paquete);
                $i++;
            }
            
            return $array;
        }

        private function getCotizarEnvio($cp = null){
            $arrayCP = $cp !=null? array("codigoPostal"=>$cp): $this->getDestinatario();
            $array = array();
            $params = array(
                "PIN" =>$this->pin,
                "idUsuario"=>  $this->idUsuario,
                "guias"=>array(
                    "remitente"=>array(
                        "codigoPostal"=>28000
                        ),
                    "consignatario"=>$arrayCP,
                    "paquetes"=> $this->getPaquetes()
                    ,
                    "tipoEntrega"=>array(
                        "id"=>2
                    ),
                    "flag"=>2
                                       
                )
            );
            //var_dump($params);
            $result = $this->redpack->cotizacion($params);

            //var_dump($result);
            foreach($result["return"]["cotizaciones"] as $clave => $valor){
                array_push($array,array("Tarifa"=>round(($valor["tarifa"]*1.15)*1.16,2),"Servicio"=>$valor["tipoServicio"]["descripcion"]));
            }
            return $array;
        }
    }

    $app = new Compras($array_principal);
    $app->main();
    