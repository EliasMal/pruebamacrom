<?php

    session_name("loginUsuario");
    session_start();
    require_once "../../../Clases/dbconectar.php";
    require_once "../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');

    class Home{
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
            
            switch($this->formulario->home->opc){
                case 'get':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Pedidos"] = $this->getNumNewPedidos();
                    $this->jsonData["Contacto"] = $this->getnumContacto();
                    $this->jsonData["Clientes"] = $this->getNumClientes();
                    $this->jsonData["Nuevosclientes"] = $this->getNuevosClientes($this->getFirstWeekDay()) ;
                    $this->jsonData["Publicados"] = $this->getNumProductos(1);
                    $this->jsonData["NoPublicados"] = $this->getNumProductos(0);

                break;
                case 'usrCON':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Usuarios"] = $this->getusrCON();
                break;
                case 'loginM':
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Usuarios"] = $this->loginM();
                break;
            }

            print json_encode($this->jsonData);
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
            $sql = "SELECT count(*) as pedidos from Pedidos where  Acreditado in (0,1)";
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