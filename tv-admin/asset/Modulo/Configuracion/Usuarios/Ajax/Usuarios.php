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

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->correo = new SendMail();
        }

        public function __destruct() {
            unset($this->conn);
        }
        
        public function principal(){
            $this->formulario = array_map("htmlspecialchars", $_POST);
            $this->foto =  isset($_FILES)? $_FILES:array();
            
            
            switch ($this->formulario["opc"]) {
                case 'new':
                        //var_dump($this->formulario);
                        $this->jsonData["Username"] = $this->getnumUser();
                        $this->jsonData["password"] = $this->create_password();
                        if($this->setUsuarios()){
                            $this->formulario["lastid"]=$this->conn->last_id();
                            if($this->setSeguridad()){
                                if(count($this->foto)!=0){
                                    $this->subirImagen();
                                }
                                $this->jsonData["Bandera"] = 1;
                                $this->jsonData["mensaje"] = "La cuenta del usuario se genero de manera satisfactoria";
                            }else{
                                $this->jsonData["Bandera"] = 0;
                                $this->jsonData["mensaje"] = "Error: No se guardo el password y usuario";
                            }
                        }else{
                            unset($this->jsonData["username"], $this->jsonData["password"]);
                            $this->jsonData["Bandera"] = 0;
                            $this->jsonData["mensaje"] = "Error: No se genero el usuario";
                        }
                    break;
                case 'save':
                    if($this->setUsuarios()){
                        $this->jsonData["Username"] = $this->formulario["Username"];
                        if(count($this->foto)!=0){
                            $this->subirImagen();
                        }
                        if($this->setSeguridad()){
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["mensaje"] = "La cuenta del usuario se ha actualizado de manera satisfactoria";
                        }
                        
                    }
                    break;
                default:
                    break;
            }
            print json_encode($this->jsonData);
        }
        
        private function getnumUser(){
            $sql = "select * from Usuarios";
            return $this->conn->query($sql);
        }
        
        private function setUsuarios(){
            if($this->formulario["opc"]=="new"){
                $sql = "INSERT INTO Usuarios (Nombre, ApPaterno, ApMaterno, Domicilio, Username, Colonia, Ciudad, Estado, Telefono, email, FechaCreacion, USRCreacion, FechaModificacion,USRModificacion, Estatus)"
                    . " values('{$this->formulario["nombre"]}','{$this->formulario["apPaterno"]}','{$this->formulario["apMaterno"]}','{$this->formulario["domicilio"]}',"
                    . "'{$this->formulario["nombre"]}','{$this->formulario["colonia"]}','{$this->formulario["ciudad"]}','{$this->formulario["estado"]}','{$this->formulario["telefono"]}','{$this->formulario["email"]}',"
                    . "'".date("Y-m-d H:i:s")."','{$_SESSION["usr"]}','".date("Y-m-d H:i:s")."','{$_SESSION["usr"]}','1')";
            }else if($this->formulario["opc"]=="save"){
                $sql = "UPDATE Usuarios SET Nombre = '{$this->formulario["Nombre"]}', ApPaterno = '{$this->formulario["ApPaterno"]}', ApMaterno = '{$this->formulario["ApMaterno"]}', Domicilio='{$this->formulario["Domicilio"]}',"
                . "Colonia='{$this->formulario["Colonia"]}', Ciudad='{$this->formulario["Ciudad"]}', Estado='{$this->formulario["Estado"]}', Telefono='{$this->formulario["Telefono"]}', email='{$this->formulario["email"]}', "
                . "FechaModificacion='". date("Y-m-d H:i:s") ."', USRModificacion='{$_SESSION["usr"]}' where _id={$this->formulario["_id"]}";
                
            }        
            return $this->conn->query($sql);
        }
        
        private function setSeguridad(){
            if($this->formulario["opc"]=="new"){
                $sql = "INSERT INTO Seguridad (username, password, Tipo_usuario, FechaCreacion, FechaModificacion, USRCreacion, USRModificacion, _idUsuarios, Estatus) values "
                    . "('{$this->formulario["nombre"]}',SHA('{$this->formulario["password"]}'),'{$this->formulario["tipousuario"]}','".date("Y-m-d H:i:s")."',"
                            . "'".date("Y-m-d H:i:s")."','{$_SESSION["usr"]}','{$_SESSION["usr"]}','{$this->formulario["lastid"]}','1')";
            }else if($this->formulario["opc"]=="save"){
                $sql = "UPDATE Seguridad SET Tipo_usuario = '{$this->formulario["Tipo_usuario"]}' where _id = ".$this->formulario["idseguridad"];
            }                
            return $this->conn->query($sql);
        }
        
        private function subirImagen(){
            //print_r($this->foto);
            if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
                $subdir ="../../../../"; 
                $dir = "Images/usuarios/";
                $archivo = $this->formulario["nombre"].".png";
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir,0755);
                }
                if($archivo && move_uploaded_file($this->foto["file"]["tmp_name"], $subdir.$dir.$archivo)){
                    //$this->rutaimagen= $dir.$archivo;
                    return true;
                }else{
                    echo "no se subio la imagen";
                }
            }else{
                return false;
            }
        }
        
        private function create_password(){
            $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
            //Obtenemos la longitud de la cadena de caracteres
            $longitudCadena=strlen($cadena);

            //Se define la variable que va a contener la contraseña
            $pass = "";
            //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
            $longitudPass=10;

            //Creamos la contraseña
            for($i=1 ; $i<=$longitudPass ; $i++){
                //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
                $pos=rand(0,$longitudCadena-1);

                //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
                $pass .= substr($cadena,$pos,1);
            }
            return $pass;
        }
    }
    
    $app = new Usuarios($array_principal);
    $app->principal();
