<?php
    session_name("loginUsuario");
    session_start();

    require_once "asset/Clases/dbconectar.php"; 
    require_once "asset/Clases/ConexionMySQL.php";

    $mi_username = $_SESSION["nombre_usuario"] ?? $_SESSION["usr"] ?? '';

    if ($mi_username != '') {
        $conn = new HelperMySql($array_principal["server"], $array_principal["user"], $array_principal["pass"], $array_principal["db"]);
        $hace_5_minutos = date("Y-m-d H:i:s", strtotime("-5 minutes"));
        
        $sql = "UPDATE Usuarios SET ultimoAcceso = '$hace_5_minutos', OnlineNow = 0 WHERE Username = '$mi_username'";
        $conn->query($sql);
    }

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
?>