<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    require_once "../../../../Clases/SendMail.php";

    date_default_timezone_set('America/Mexico_City');
    

    class Usuarios{
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();
        private $correo;
        private $obj;
        
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->correo = new SendMail();
        }

        public function __destruct() {
            unset($this->conn);
        }
        
        public function principal(){
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            switch ($this->formulario->usuarios->opc) {
                case 'buscar':
                    $datos = $this->getUsuarios();
                    if(count($datos)){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Entro a la funcion";
                        $this->jsonData["Data"] = $datos;
                    }
                    break;
                case 'activar':
                case 'borrar':
                        if($this->setUsuarios($this->formulario->usuarios->opc=="borrar"? 0:1)){
                            if($this->setSeguridad($this->formulario->usuarios->opc=="borrar"? 0:1)){
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] = "El usuario se a desactivado";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: al desactivar la seguridad";
                            }
                        }else{
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error: al desactivar el usuario";
                        }
                    break;
                case 'edit':
                    //var_dump($this->formulario);
                    $dato = $this->getUsuario();
                    if(count($dato)){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["Data"] = $dato;
                        $this->jsonData["img"] = file_exists("../../../../Images/usuarios/".$this->formulario->usuarios->id.".png");
                    }
                    break;
                case 'pass':
                    if($this->setPassword()){
                        $this->Sendusernamexemail($this->formulario->usuarios->nombre, $this->formulario->usuarios->username, 
                        $this->formulario->usuarios->pass, $this->formulario->usuarios->email);
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "El password ha sido cambiado";
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error al intentar cambiar el password";
                    }
                    break;
                default:
                    break;
            }
            print json_encode($this->jsonData);
        }
        
        private function Sendusernamexemail($nombre, $username, $pass, $email){
            $subjet = "Recuperacion de password";
            $correos = array();
            $body = file_get_contents("../../../../View/Resetusuario.html");
            $body = str_replace('{nombre}',$nombre,$body);
            $body = str_replace('{username}',$username, $body);
            $body = str_replace('{password}',$pass, $body);
            array_push($correos, array("email"=>$email, "nombre"=>$nombre)); 
            $this->correo->Send($subjet,$correos,$body);
        }
        
        private function getUsuarios(){
            $array = array();
            if ($_SESSION["rol"]=="root"){
                $sql = "select US.*,SG.Tipo_usuario from Usuarios as US inner join Seguridad as SG on (SG._idUsuarios = US._id) where US.Username not in ('root') and US.estatus = " . $this->formulario->usuarios->historico;
            }else if($_SESSION["rol"]=="Admin"){
                $sql = "select US.*,SG.Tipo_usuario from Usuarios as US inner join Seguridad as SG on (SG._idUsuarios = US._id) where US.Username not in ('Admin','root') and US.estatus = ". $this->formulario->usuarios->historico;
            }else{
                
            }
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getUsuario(){
            
            $sql = "Select *,SG._id as idseguridad from Usuarios as US inner join Seguridad as SG on (SG._idUsuarios = US._id) where US._id=".$this->formulario->usuarios->id;
            return $this->conn->fetch($this->conn->query($sql));
        }
        
        private function setUsuarios($estatus = 0){
            $sql = "UPDATE Usuarios SET estatus = $estatus where _id={$this->formulario->usuarios->id}";
            return $this->conn->query($sql);
        }
        
        private function setSeguridad($estatus = 0){
            $sql = "UPDATE Seguridad SET estatus = $estatus where _idUsuarios={$this->formulario->usuarios->id}";
            return $this->conn->query($sql);
        }
        
        private function setPassword(){
            $sql = "UPDATE Seguridad SET password=SHA('".$this->formulario->usuarios->pass."') where _id=".$this->formulario->usuarios->id;
            return $this->conn->query($sql);
        }
        
    }
    
    $app = new Usuarios($array_principal);
    $app->principal();
    
    

