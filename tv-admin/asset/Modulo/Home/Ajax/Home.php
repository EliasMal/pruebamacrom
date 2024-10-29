<?php
    require_once "../../../Clases/dbconectar.php";
    require_once "../../../Clases/ConexionMySQL.php";
    session_name("loginUsuario");
    session_start();
    date_default_timezone_set('America/Mexico_City');

    class Home{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();
        private $currentDateM = array();
        private $DateBD = array();
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }

        public function main(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            switch($this->formulario->home->opc){
                case 'get':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Pedidos"] = $this->getNumNewPedidos();
                    $this->jsonData["Contacto"] = $this->getnumContacto();
                    $this->jsonData["Clientes"] = $this->getNumClientes();
                    $this->jsonData["Nuevosclientes"] = $this->getNuevosClientes($this->getFirstWeekDay()) ;
                    $this->jsonData["Publicados"] = $this->getNumProductos(1);
                    $this->jsonData["NoPublicados"] = $this->getNumProductos(0);
                    $this->jsonData["todos"] = $this->getUsers();

                break;
                case 'usrCON':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Usuarios"] = $this->getusrCON();
                break;
                case 'loginM':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Usuarios"] = $this->loginM();
                break;
                case 'isonline':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["isonline"] = $this->isonline();
                break;
            }

            print json_encode($this->jsonData);
        }

        private function isonline(){
            $sql = "SELECT OnlineNow FROM Usuarios WHERE _id = '{$_SESSION["_id"]}' and Username = '{$_SESSION["usr"]}'";
            $result = $this->conn->query($sql);
            $isonline = $result->fetch_array()[0];
            if($isonline == 1){
                $this->isonlineKill();
                session_destroy();
            }
        }

        private function getUsers(){
            $sql = "SELECT * FROM Usuarios where _id != '{$_SESSION["_id"]}'";
            $result = $this->conn->fetch_all($this->conn->query($sql));
            foreach ($result as $valor) {
                $currentDateM[0] = $currentDate = date("Y-n-j H:i:s");
                $DateBD[0] = date($valor["ultimoAcceso"]);
                $currentDateM[1] = strtotime($currentDateM[0]);
                $currentDateM[2] = date("m:d:H", $currentDateM[1]);
                $DateBD[1] = strtotime($DateBD[0]);
                $DateBD[2] = date("m:d:H", $DateBD[1]);

                if($currentDateM[2] == $DateBD[2]){
                    $DateBD[3] = strtotime('+15 minute', $DateBD[1]);
                    $DateBD[3] = date('Y-n-j H:i:s', $DateBD[3]);
                    if($currentDateM[0] >= $DateBD[3]){
                        $this->actualizarOnlineNow($valor["_id"]);
                    }
                }else{
                    $this->actualizarOnlineNow($valor["_id"]);
                }
            }
            return $result;
        }

        private function actualizarOnlineNow($_id){
            $sql = "UPDATE Usuarios SET OnlineNow = 0 where _id = $_id";
            return $this->conn->query($sql);
        }

        private function isonlineKill(){
            $sql = "UPDATE Usuarios SET OnlineNow = 0 where _id = '{$_SESSION["_id"]}' and Username = '{$_SESSION["usr"]}'";
            return $this->conn->query($sql);
        }

        private function loginM(){
            $sql = "select count(Estatus) as NEstatus from Seguridad where Estatus = 1";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["NEstatus"];
        }

        private function getusrCON(){
            $sql = "select * FROM Seguridad where username = '{$_SESSION['usr']}'";
            return $this->conn->fetch($this->conn->query($sql));
        }
        
        private function getNumProductos($publicados){
            $sql = "select count(*) as total from Producto where Estatus = 1 and Publicar = $publicados";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["total"];
        }

        private function getnumContacto(){
            $sql = "select if(count(_id)>0,count(_id),0) as noleidos from Contacto where leido = 0";
            $row = $this->conn->fetch($this->conn->query($sql));
            
            return $row["noleidos"];
        }
        
        private function getNumClientes(){
            $sql = "select count(_id) as clientes from clientes where Estatus = 1";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["clientes"];
        }

        private function getNuevosClientes($week_start){
            $row = [];
            $sql  = "select count(_id) as nuevos from clientes where FechaCreacion >= '$week_start'";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["nuevos"];
        }

        private function getNumNewPedidos(){
            $sql = "SELECT count(*) as pedidos from Pedidos where Acreditado in (0,1)";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["pedidos"];
        }

        private function getFirstWeekDay(){
            if(date("D")=="Mon"){
                $week_start = date("Y-m-d");
            }else{
                $week_start = date("Y-m-d", strtotime("last Monday", time()));
            }
            return $week_start;
        }
    }

    $app = new Home($array_principal);
    $app->main();