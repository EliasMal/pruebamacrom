<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";

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
            $sql = "select C._id as id, C.nombres, C.Apellidos, C.correo, CS.cupon_nombre from Cseguridad as CS 
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

            if($temp == NULL){
                $Cenvio["Envio"] = "N";
                $Cenvio["Costos"] = $this->getCotizarEnvio($cp);
                $Cenvio["Servicio"] = "NACIONAL";
            }else if(count($temp) > 0){
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
            $dataArray["dataCP"] = $this->getDataCP((int)$dataArray["datadomicilio"]["data"]["Codigo_postal"]);
            //var_dump("antes de Cenvio");
            return $dataArray;
        }

        public function main (){
            $this->formulario = json_decode(file_get_contents('php://input'));
            switch ($this->formulario->Compras->opc) {

                case 'get':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getDataUser($this->formulario->Compras->username);
                break;
                
                case 'cotizar':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getCotizarEnvio();
                break;
                
                case 'validarCupon':
                    $this->jsonData = $this->validarCupon();
                break;
                
                default:
                    $this->jsonData = [
                        "Bandera" => 0,
                        "mensaje" => "Operación no válida"
                    ];
                break;
            } 
            print json_encode($this->jsonData);
        }

        private function validarCupon(){
            $codigo = trim($this->formulario->Compras->cupon);
            $idCliente = (int)$this->formulario->Compras->id;

            if ($codigo == "") {
                return [
                    "Bandera" => 0,
                    "mensaje" => "Ingresa un cupón"
                ];
            }
            //Buscar cupón activo
            $sql = "
                SELECT id, codigo, descuento, uso_unico, fecha_expiracion, es_global
                FROM cupones
                WHERE codigo = '$codigo'
                AND activo = 1
                LIMIT 1
            ";

            $cupon = $this->conn->fetch($this->conn->query($sql));

            if (!$cupon) {
                return [
                    "Bandera" => 0,
                    "mensaje" => "Cupón no válido"
                ];
            }
            //Validar expiración
            if (!empty($cupon["fecha_expiracion"])) {
                if (strtotime($cupon["fecha_expiracion"]) < strtotime(date("Y-m-d"))) {
                    return [
                        "Bandera" => 0,
                        "mensaje" => "Este cupón ha expirado"
                    ];
                }
            }
            //Validar asignación (global o cliente)
            if (!$this->clientePuedeUsarCupon($idCliente, $cupon["id"], $cupon["es_global"])) {
                return [
                    "Bandera" => 0,
                    "mensaje" => "Este cupón no está disponible para tu cuenta"
                ];
            }
            //Validar uso único (SIN registrar)
            if ((int)$cupon["uso_unico"] === 1) {

                $sqlUso = "
                    SELECT id
                    FROM cupones_usados
                    WHERE id_cupon = {$cupon["id"]}
                    AND id_cliente = $idCliente
                    LIMIT 1
                ";

                $usado = $this->conn->fetch($this->conn->query($sqlUso));

                if ($usado) {
                    return [
                        "Bandera" => 0,
                        "mensaje" => "Este cupón ya fue utilizado"
                    ];
                }
            }
            //Cupón válido (NO se guarda todavía)
            return [
                "Bandera" => 1,
                "mensaje" => "Cupón aplicado correctamente",
                "descuento" => (int)$cupon["descuento"],
                "codigo" => $cupon["codigo"],
                "id_cupon" => (int)$cupon["id"]
            ];
        }

        private function clientePuedeUsarCupon($idCliente, $idCupon, $esGlobal){
            if($esGlobal == 1){
                return true;
            }

            $sql = "
                SELECT id 
                FROM clientes_cupones
                WHERE id_cliente = $idCliente
                AND id_cupon = $idCupon
                AND activo = 1
                LIMIT 1
            ";

            $row = $this->conn->fetch($this->conn->query($sql));
            return $row ? true : false;
        }

        private function getDataCP($cp = null){
            $sql = "select d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_estado, d_ciudad from CPmex where d_codigo = $cp";
            $row = $this->conn->fetch_all($this->conn->query($sql));
            return $row;
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
            foreach($_SESSION["CarritoPrueba"] as $key => $value){
                $paquete = array(
                    "largo" => (int)$value["Largo"],
                    "ancho" => (int)$value["Cantidad"]*(int)$value["Ancho"],
                    "alto" => (int)$value["Alto"],
                    "peso" => $value["Cantidad"]*$value["Peso"],
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
    