<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("asset/Clases/dbconectar.php");
include("asset/Clases/ConexionMySQL.php");

$conexion = New HelperMySql($array_principal["server"],$array_principal["user"],$array_principal["pass"],$array_principal["db"]);
$sql = "SELECT * from Seguridad";
$id = $conexion->query($sql);

if($conexion->count_rows($id)!=0){
    header("Location: Login/login.php");
}else{
    
    $sql = "INSERT INTO Usuarios(Nombre, ApPaterno, ApMaterno, Domicilio, Colonia,Ciudad, Estado, Telefono, "
            . "email, Username, FechaCreacion, USRCreacion, FechaModificacion, USRModificacion, Estatus)"
            . "values('Francisco Ivan','Ramirez','Alcaraz','','','','','','','root','". date("Y-m-d H:i:s") 
            . "','SYS','".date("Y-m-d H:i:s")."','SYS','1')"; 
    $id=$conexion->query($sql);
        
    $lastid = $conexion->last_id($id);
    
    $sql = "INSERT INTO Seguridad(username,password,Tipo_usuario,FechaCreacion,"
            . "FechaModificacion, USRCreacion,USRModificacion, _idUsuarios, Estatus)"
            . "value('root',SHA('9804520k'), 'root','".date("Y-m-d H:i:s")."','"
            .date("Y-m-d H:i:s")."','SYS','SYS',$lastid, '1')";
    $conexion->query($sql);
    
    /*Creacion de la cuenta de admin*/
    $sql = "INSERT INTO Usuarios(Nombre, ApPaterno, ApMaterno, Domicilio, Colonia,Ciudad, Estado, Telefono, "
            . "email, Username, FechaCreacion, USRCreacion, FechaModificacion, USRModificacion, Estatus)"
            . "values('Administrador','','','','','','','','','Admin','". date("Y-m-d H:i:s") 
            . "','SYS','".date("Y-m-d H:i:s")."','SYS','1')";
    $id=$conexion->query($sql);
    
    $lastid = $conexion->last_id($id);
    
    $sql = "INSERT INTO Seguridad(username,password,Tipo_usuario,FechaCreacion,"
            . "FechaModificacion, USRCreacion,USRModificacion, _idUsuarios, Estatus)"
            . "value('Admin',SHA('12345'), 'Admin','".date("Y-m-d H:i:s")."','"
            .date("Y-m-d H:i:s")."','SYS','SYS',$lastid, '1')";
    $conexion->query($sql);
    
    
    header("Location: index.php");
}

