<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";

require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
require_once '../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
date_default_timezone_set('America/Mexico_City');

class Login{
    private $conn;
    private $formulario = array();
    private $jsonData = array("mensaje"=>"", "Bandera" => 0, "Olvidado" => 0);
    private $dataLogin = array();
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    private function getIP(){
        return $_SERVER['REMOTE_ADDR'];
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        switch($this->formulario->Login->opc){
            case 'in':
                $userInput = $this->formulario->Login->user;
                            
                if($this->estaBloqueado($userInput)){

                    $info = $this->getIntentosInfo($userInput);

                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Cuenta bloqueada temporalmente.";
                    $this->jsonData["bloqueado"] = 1;
                    $this->jsonData["intentos_restantes"] = 0;
                    $this->jsonData["tiempo_restante"] = $info["tiempo_restante"];

                    break;
                }
                            
                if($this->getUser()){
                    $this->limpiarIntentos($userInput);
                            
                    if($this->setSession(true)){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Bienvenido '{$this->dataLogin["nombres"]}'";
                        $this->jsonData["session"] = $_SESSION;
                    }
                            
                } else {
                    $this->registrarIntentoFallido($userInput);
                            
                    $info = $this->getIntentosInfo($userInput);
                            
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Usuario o contraseña incorrectos";
                    $this->jsonData["bloqueado"] = $info["bloqueado"];
                    $this->jsonData["intentos_restantes"] = $info["intentos_restantes"];
                    $this->jsonData["tiempo_restante"] = $info["tiempo_restante"];
                }
                break;
            case 'out':
                if($this->outSession()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "La session se cerro";
                    
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error: No se pudo eliminar la session";
                }
                break;
            case 'forgot':
            
                if(empty($this->formulario->Login->user)){
                    $this->jsonData["Olvidado"] = 0;
                    $this->jsonData["mensaje"] = "Ingresa tu correo";
                    break;
                }
            
                $user = addslashes($this->formulario->Login->user);
            
                $sql = "SELECT _id FROM Cseguridad WHERE username = '$user' LIMIT 1";
                $result = $this->conn->query($sql);
                $userData = $this->conn->fetch($result);
            
                // Siempre respondemos lo mismo (anti enumeración)
                $this->jsonData["Olvidado"] = 1;
                $this->jsonData["mensaje"] = "Si el correo existe, recibirás instrucciones para restablecer tu contraseña.";
            
                if(!$userData){
                    break;
                }
            
                $token = bin2hex(random_bytes(32));
                $expira = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            
                $insert = "INSERT INTO password_resets (user_id, token, expires_at)
                           VALUES ('{$userData["_id"]}', '$token', '$expira')";
                $this->conn->query($insert);
            
                $link = "https://macromautopartes.com/reset-password.php?token=".$token;
            
                $this->enviarCorreoReset($user, $link);
            
                break;
        }
        header('Content-Type: application/json');
        print json_encode($this->jsonData);
    }

    private function enviarCorreoReset($destinatario, $link){
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'soporte@macromautopartes.com';
        $mail->Password = SMTP_PASS;
        $mail->setFrom('soporte@macromautopartes.com', 'Soporte Macrom');
        $mail->addAddress($destinatario);
        $mail->Subject = 'Restablecer contraseña - Macromautopartes';
        $mail->IsHTML(true);
        $mail->CharSet = 'utf-8';

        $mail->Body = "
            <h2>Restablecer contraseña</h2>
            <p>Haz clic en el siguiente enlace:</p>
            <a href='$link'>$link</a>
            <p>Este enlace expira en 15 minutos.</p>
        ";

        $mail->send();
    }

    private function estaBloqueado($user){

        $ip = $this->getIP();
        $user = addslashes($user);

        $sql = "SELECT intentos, bloqueado_hasta FROM login_intentos WHERE username='$user' LIMIT 1";

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

        $ip = $this->getIP();
        $user = addslashes($user);

        $sql = "SELECT intentos, bloqueado_hasta FROM login_intentos WHERE username='$user' LIMIT 1";
        $result = $this->conn->query($sql);
        $data = $this->conn->fetch($result);

        $maxIntentos = 5;

        if(!$data){
            return [
                "intentos_restantes" => $maxIntentos,
                "bloqueado" => 0,
                "tiempo_restante" => 0
            ];
        }

        $intentos = intval($data["intentos"]);
        $restantes = max(0, $maxIntentos - $intentos);

        if($data["bloqueado_hasta"] && strtotime($data["bloqueado_hasta"]) > time()){
            $segundos = strtotime($data["bloqueado_hasta"]) - time();

            return [
                "intentos_restantes" => 0,
                "bloqueado" => 1,
                "tiempo_restante" => $segundos
            ];
        }

        return [
            "intentos_restantes" => $restantes,
            "bloqueado" => 0,
            "tiempo_restante" => 0
        ];
    }

    private function registrarIntentoFallido($user){

        $ip = $this->getIP();
        $user = addslashes($user);
        $ahora = date("Y-m-d H:i:s");

        $sql = "SELECT * FROM login_intentos WHERE username='$user' LIMIT 1";

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

            $insert = "INSERT INTO login_intentos (username, intentos, ultimo_intento) VALUES ('$user',1,'$ahora')";
            $this->conn->query($insert);
        }
    }

    private function limpiarIntentos($user){

        $ip = $this->getIP();
        $user = addslashes($user);

        $sql = "DELETE FROM login_intentos WHERE username='$user'";
        $this->conn->query($sql);
    }
                
    private function getUser(){

        if(empty($this->formulario->Login->user) || empty($this->formulario->Login->password)){
            return false;
        }

        $user = addslashes($this->formulario->Login->user);
        $pass = $this->formulario->Login->password;

        $sql = "SELECT CS.password, CS._id, CS._id_cliente, CS.password_changed_at,
                       C.nombres, C.apellidos, C.Codigo_postal
                FROM Cseguridad CS
                INNER JOIN clientes C ON CS._id_cliente = C._id
                WHERE CS.username = '$user'
                LIMIT 1";

        $result = $this->conn->query($sql);

        if(!$result){
            return false;
        }

        $userData = $this->conn->fetch($result);

        if(!$userData){
            return false;
        }

        $passwordGuardado = $userData["password"];

        // 🔐 Password moderno
        if(password_verify($pass, $passwordGuardado)){
            $this->dataLogin = $userData;
            return true;
        }

        // 🔄 SHA1 viejo
        if($passwordGuardado === sha1($pass)){

            $nuevoHash = password_hash($pass, PASSWORD_DEFAULT);

            $update = "UPDATE Cseguridad 
                       SET password = '$nuevoHash'
                       WHERE _id = '".$userData["_id"]."'";

            $this->conn->query($update);

            $this->dataLogin = $userData;
            return true;
        }

        return false;
    }
    
    private function setSession($flag = false){
        if($flag){

            session_regenerate_id(true);

            $_SESSION["padlock"] = "lock";
            $_SESSION["password_changed_at"] = $this->dataLogin["password_changed_at"];
            $_SESSION["autentificacion"]=1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $this->dataLogin["nombres"];
            $_SESSION["nombre"] = $this->dataLogin["nombres"].' '.$this->dataLogin["apellidos"];
            $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
            $_SESSION["CarritoPrueba"] = $this->get_Carrito();
            $_SESSION["Cenvio"] = $this->getCenvio();
            $this->jsonData["Productos"]= $this->productos();

            if($this->DomIn() == NULL){
                $_SESSION["id_domicilio"] = 0;
            }else{
                $_SESSION["id_domicilio"] = $this->DomIn();
            }

            $_SESSION["usr"] = $this->formulario->Login->user;

            $sql ="UPDATE clientes 
                   SET ultimoacceso = '{$_SESSION["ultimoAcceso"]}' 
                   WHERE _id = '{$this->dataLogin["_id_cliente"]}'";

            return $this->conn->query($sql);
        }else{
            return false;
        }
    }
    
    private function get_Carrito(){
        $array = array();
        
        $sql = "SELECT DISTINCT _clienteid, CR.Clave, CR.No_parte, CR.Cantidad, CR.Precio, CR.Precio2, P.RefaccionOferta, 
        CR.Producto as _producto, CR.Alto, CR.Largo, CR.Ancho, CR.Peso, CR.imagenid, CR.Existencias,
        P.Kit, P.stock as StockBD, P.precio_manual, P.Precio1 as Precio1BD, P.Precio2 as Precio2BD 
        FROM Carrito CR left JOIN Producto as P on P.Clave = CR.Clave where _clienteid='{$this->dataLogin["_id_cliente"]}' and _clienteid != 0";
        
        $id = $this->conn->query($sql);
        if($id){
            while ($row = $this->conn->fetch($id)){
                
                if($row['Kit'] == 1 || $row['Kit'] == '1'){
                    $stockReal = intval($row['StockBD']);
                    $row['Existencias'] = $stockReal; 
                    
                    if(intval($row['Cantidad']) > $stockReal){
                        $row['Cantidad'] = $stockReal > 0 ? $stockReal : 1; 
                        $updateSql = "UPDATE Carrito SET Existencias = '$stockReal', Cantidad = '{$row['Cantidad']}' WHERE Clave = '{$row['Clave']}' AND _clienteid = '{$this->dataLogin["_id_cliente"]}'";
                        $this->conn->query($updateSql);
                    }
                }

                if($row['Kit'] == 1 || $row['Kit'] == '1' || $row['precio_manual'] == 1 || $row['precio_manual'] == '1'){
                    $row['Precio'] = $row['Precio1BD'];
                    $row['Precio2'] = $row['Precio2BD'];
                    
                    $updateSqlPrice = "UPDATE Carrito SET Precio = '{$row['Precio1BD']}', Precio2 = '{$row['Precio2BD']}' WHERE Clave = '{$row['Clave']}' AND _clienteid = '{$this->dataLogin["_id_cliente"]}'";
                    $this->conn->query($updateSqlPrice);
                }

                array_push($array, $row);
            }
        }
        return $array;
    }

    private function DomIn(){
        $sql = "SELECT _id FROM Cdirecciones where _id_cliente = '{$this->dataLogin["_id_cliente"]}' and Predeterminado = 1";
        return $this->conn->fetch($this->conn->query($sql));
    }

    private function productos(){ /* reparar*/
        $sql = "SELECT * FROM Producto where Clave = '{$this->formulario->Login->clave}'";
        return $this->conn->fetch_all($this->conn->query($sql));
    }

    private function getCenvio(){
        $array = array("Envio"=>"","costo"=>0, "Servicio"=>"") ;
        $sql = "select CE.precio from Cenvios as CE 
        inner join CPmex as CP on (CP.D_mnpio = CE.Municipio)
        where CP.d_codigo = '{$this->dataLogin["Codigo_postal"]}' group by CE.precio";
        $id = $this->conn->query($sql);
        if($this->conn->count_rows() != 0){
            $row = $this->conn->fetch();
            $array["Envio"] = "L"; //Envio Local
            $array["costo"] = floatval($row["precio"]);
            $array["Servicio"] = "METROPOLITANO";
        }else{
            $array["Envio"] = "N"; //Envio nacional
            $array["costo"] = 0;
        }
        return $array;
    }

    private function outSession(){
        $_SESSION = [];
        session_destroy();
        return true;
    }
}

$app = new Login($array_principal);
$app->main();