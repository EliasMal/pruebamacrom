<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
require_once "../../../tv-admin/asset/Clases/dbconectar.php";
require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Login{
    private $conn;
    private $formulario = array();
    private $jsonData = array("mensaje"=>"", "Bandera" => 0, "Olvidado" => 0);
    private $dataLogin = array();
    private $dataLoginolv = array();
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        switch($this->formulario->Login->opc){
            case 'in':
                if($this->setSession($this->getUser())){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Bienvenido '{$this->dataLogin["nombres"]}'";
                    $this->jsonData["session"] = $_SESSION;
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Error: el usuario no existe";
                }
                break;
            case 'out':
                if($this->outSession()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mansaje"] = "La session se cerro";
                    
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mansaje"] = "Error: No se pudo eliminar la session";
                }
                break;
            case 'forgot':
                $this->getUserOlvidado();
                if(strlen($this->dataLoginolv["username"]) > 6){

                    if($this->set_Password($this->getUser())){
                        $this->jsonData["Olvidado"] = 1;
                        $this->jsonData["mensaje"] = "Correo de recuperación enviado, revisa tu correo electronico";
                    } else{
                        $this->jsonData["Olvidado"] = 0;
                        $this->jsonData["mensaje"] = $this->dataLogin["username"];
                    }

                }else{
                    $this->jsonData["Olvidado"] = 0;
                    $this->jsonData["mensaje"] = "Error: el usuario no existe";
                }  
                break;
        }
        print json_encode($this->jsonData);
    }

    private function set_Password(){
        $palabras = array("macrom3647", "refaccion2573", "colima2457","manzanillo2457","autopartes2457","webdesign19256");
        $random = rand(0, 5);
        $contra = str_shuffle($palabras[$random]);
        $contrasenanueva = sha1($contra);
        //Envio de registro satisfactorio al Correo del usuario.
        $destinatario =$this->formulario->Login->user;
        $asunto='Cambio de Contraseña en Macromautopartes';
        $newpass = $contra;
        $mensaje= '<!DOCTYPE html>
        <html lang="es">
        <head>
        </head>
            <body>
                <style>
                    .contmenubus, footer, .copyseccion, #myBtn, .carritoshop, .menudesinsreg, .header2, .ft0{display: none;visibility: collapse;height:-100%;width:-100%;}
                    .dpitm{display: flex;justify-content: space-evenly;}
                    .pofam{font-family: Poppins;}
                </style>
                    <div>
                        <section style="padding-bottom:60px;">
                            <div class="container1" style="width:1000px;">
                                <div class="row">
                                    <div class="col-md-6 insmob" style="padding-bottom:30px;">
                                        <form name="frmReg" id="frmReg"  novalidate>
                                            <h4><img src="https://macromautopartes.com/images/icons/CRcabecera.png" style="width:100%;"></h4>
                                                <div style="color:#de0007;text-align:center;">
                                                    <h4 class="pofam" style="font-size:25px;line-height:32px;margin-bottom:0px;">Tu Contraseña ha sido</h4>
                                                    <h4 class="pofam" style="font-size:25px;margin-top:0px">restablecida</h4>
                                                </div>
                                                <h4 style="text-align:center;"><img src="https://macromautopartes.com/images/usuarios/Avatar%20Lobo%20Macrom%20Grande.png" style="height: 250px;"></h4>
                                                <div>
                                                    <h4 class="pofam" style="color:#757575;text-align:center;font-size:22px;margin-bottom:0px;">¿Olvidaste tu contraseña?</h4>
                                                    <h4 class="pofam" style="color:#9e9e9e;text-align:justify;font-size:20px;margin-top:0px; line-height:1.7;">Se genero una contraseña aleatoria, ingresa a tu cuenta en Macromautopartes.com con esta nueva contraseña, dirígete a la sección SESSION Y SEGURIDAD, y cambia la contraseña autogenerada por una personal.</h4>
                                                    <h4 class="pofam" style="color:#757575;text-align:center;font-size:22px;margin-bottom:0px;">Contraseña Autogenerada: '.$newpass.'</h4>
                                                </div>
                                            <h4 class="m-text26 prueba3" style="padding-bottom:42px;"><img src="https://macromautopartes.com/images/icons/CRPie-pagina.png" style="width:100%;"></h4>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
            </body>
        </html>';
        $email = "webmaster@macromautopartes.com";
        $headers ="MIME.Version: 1.0". "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8". "\r\n";
        mail($destinatario, $asunto, $mensaje, $headers);
        //Fin Envio de registro satisfactorio al Correo del usuario.
        
        $sql = "UPDATE Cseguridad SET password = '$contrasenanueva' WHERE _id = '{$this->dataLoginolv["_id"]}' and username = '{$this->formulario->Login->user}'";
        return $this->conn->query($sql)? true:false;
        
    }
    
    private function getUser(){
        $sql = "SELECT C.nombres, C.apellidos, C.Codigo_postal, CS._id_cliente, CS.cuponacre, CS._id FROM Cseguridad as CS inner join clientes as C on (CS._id_cliente = C._id)
            where username='{$this->formulario->Login->user}' and password='". sha1($this->formulario->Login->password)."'";
        $this->dataLogin = $this->conn->fetch($this->conn->query($sql));
        
        return count($this->dataLogin)!=0? true:false;
    }

    private function getUserOlvidado(){
        $sql = "SELECT * FROM Cseguridad where username='{$this->formulario->Login->user}'";
        $this->dataLoginolv = $this->conn->fetch($this->conn->query($sql));
        
        return count($this->dataLoginolv)!=0? true:false;
    }
    
    private function setSession($flag = false){
        if($flag){
            session_name("loginCliente");
            session_start();
            $_SESSION["padlock"] = "lock";
            $_SESSION["autentificacion"]=1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $this->dataLogin["nombres"];
            $_SESSION["nombre"] = $this->dataLogin["nombres"].' '.$this->dataLogin["apellidos"];
            $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
            $_SESSION["Cenvio"] = $this->getCenvio();
            $_SESSION["usr"] = $this->formulario->Login->user;
            $sql ="UPDATE clientes SET ultimoacceso = '{$_SESSION["ultimoAcceso"]}' where _id = '{$this->dataLogin["_id_cliente"]}' and correo = '{$this->formulario->Login->user}'";
            return $this->conn->query($sql);
        }else{
            return false;
        }
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
        session_name("loginCliente");
        session_start();
        session_destroy();
        return true;
    }
}

$app = new Login($array_principal);
$app->main();