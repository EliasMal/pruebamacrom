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

class Registro{
    private $conn;
    private $formulario = array();
    private $jsonData = array("mensaje"=>"", "Bandera" => 0);
    
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    } 
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
        if($this->FindCliente()){
            $id = $this->setCliente();
            if($id){
                    if($this->setSession($this->setCSeguridad($id))){
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Bienvenido ". $this->formulario->Registro->Nombre;
                        $this->jsonData["Session"] = $_SESSION;
                        //Envio de registro satisfactorio al Correo del usuario.
                        $destinatario =$this->formulario->Registro->username;
                        $nombre = $this->formulario->Registro->Nombre;
                        $asunto='Correo registrado en Macromautopartes';
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
                                        <section style="padding-bottom:60px;>
                                            <div class="container1" style="width:1000px;">
                                                <div class="row">
                                                    <div class="col-md-6 insmob" style="padding-bottom:30px;">
                                                        <form name="frmReg" id="frmReg"  novalidate>
                                                            <h4><img src="https://macromautopartes.com/images/icons/CRcabecera.png" style="width:100%;"></h4>
                                                                <div style="color:#de0007;text-align:center;">
                                                                    <h4 class="pofam" style="font-size:25px;line-height:32px;margin-bottom:0px;">Te has registrado de manera</h4>
                                                                    <h4 class="pofam" style="font-size:25px;margin-top:0px">exitosa</h4>
                                                                </div>
                                                                <h4 style="text-align:center;"><img src="https://macromautopartes.com/images/icons/CR-caja.png" style="height: 250px;"></h4>
                                                                <div>
                                                                    <h4 class="pofam" style="color:#757575;text-align:center;font-size:22px;margin-bottom:0px;">Tu cuenta está lista para usarse</h4>
                                                                    <h4 class="pofam" style="color:#9e9e9e;text-align:center;font-size:20px;margin-top:0px;">Comienza a comprar desde la comodidad de tu casa.</h4>
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
                    }else{
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "Error: al iniciar session";
                    }
                    
            }else{
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["mensaje"] = "Error: No se creo el usuario";
            }
        }else{
            
            $this->jsonData["Bandera"] = 0;
            $this->jsonData["mensaje"] = "El usuario que se quiere registrar ya existe";
        }
        print json_encode($this->jsonData);
    }
    
    private function FindCliente(){
        $sql = "Select * from clientes where correo = '". $this->formulario->Registro->username."'";
        
        $this->conn->query($sql);
        return $this->conn->count_rows()>0? false: true;
    }
    
    private function setCliente(){
        $fecha1 = date("Y-m-d",strtotime($this->formulario->Registro->FechaCreacion));
        $this->formulario->Registro->Aviso = $this->formulario->Registro->Aviso == "true"? 1:0;
        $sql = "INSERT INTO clientes(nombres, Apellidos, correo, Domicilio, Colonia, Codigo_postal, ciudad, estado, "
                . "telefono, FechaCreacion, FechaModificacion, ultimoacceso, inicioacceso, Estatus, avisoprivacidad) value "
                . "('{$this->formulario->Registro->Nombre}','{$this->formulario->Registro->Apellidos}','{$this->formulario->Registro->username}',"
                . "'{$this->formulario->Registro->Domicilio}','{$this->formulario->Registro->Colonia}','{$this->formulario->Registro->Codigopostal}',"
                . "'{$this->formulario->Registro->Ciudad}','{$this->formulario->Registro->Estado}','{$this->formulario->Registro->Telefono}',"
                . "'".date("Y-m-d", strtotime($this->formulario->Registro->FechaCreacion))."','".date("Y-m-d", strtotime($this->formulario->Registro->FechaModificacion))."',"
                . "'".date("Y-m-d", strtotime($this->formulario->Registro->ultimoaccesso))."','".date("Y-m-d", strtotime($this->formulario->Registro->inicioacceso))."',1,"
                . "'{$this->formulario->Registro->Aviso}')";
        return $this->conn->query($sql)? $this->conn->last_id():false;
    }
    
    private function setCSeguridad ($id){
        $sql = "INSERT INTO Cseguridad(username, password, FechaCreacion, FechaModificacion, Estatus, _id_cliente, cuponacre, cupon_nombre) values "
                . "('{$this->formulario->Registro->username}',SHA('{$this->formulario->Registro->pass}'),'"
                . date("Y-m-d", strtotime($this->formulario->Registro->FechaCreacion))."','".date("Y-m-d", strtotime($this->formulario->Registro->FechaModificacion))."',1,'$id',0,null)";
        return $this->conn->query($sql) ? true: false;
         
    }
    
    private function setSession($flag = false){
        if($flag){
            session_name("loginCliente");
            session_start();
            $_SESSION["autentificacion"]=1;
            $_SESSION["ultimoAcceso"]= date("Y-n-j H:i:s");
            $_SESSION["nombrecorto"] = $this->formulario->Registro->Nombre;
            $_SESSION["nombre"] = $this->formulario->Registro->Nombre.' '.$this->formulario->Registro->Apellidos;
            $_SESSION["iduser"] = $id;
            $_SESSION["usr"] = $this->formulario->Registro->username;
            return true;
        }else{
            return false;
        }
    }
    
}

$app = new Registro($array_principal);
$app->main();