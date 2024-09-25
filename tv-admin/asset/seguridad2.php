<?php
/*Este modulo solo debe de usarse con los modulos de ajax de la carpeta modulo*/
session_name("loginUsuario");
session_start();
date_default_timezone_set('America/Mexico_City');
if($_SESSION["autentificacion"]!=1){
    header("Location: ../../../../redireccionar.php");
}else{
//        print_r($_SESSION);
    $duracionsession = 10; //duracion en minutos
    $fechaGuarda = $_SESSION["ultimoAcceso"];
    $ahora = date("Y-n-j H:i:s");
    $tiempotranscurrido = (strtotime($ahora)-strtotime($fechaGuarda));
    if($tiempotranscurrido >= ($duracionsession*60)){
        /*Si es mayor el timpo destruyo la sesion y lo mando al index de nuevo*/
        header("Location: ../../../../redireccionar.php");
    }else{
    $_SESSION["ultimoAcceso"] = $ahora;
    }
}