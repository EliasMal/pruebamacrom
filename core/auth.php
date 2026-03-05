<?php
/**
 * AUTH GLOBAL
 * Protege páginas privadas y valida cambios de contraseña
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/conf.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/tv-admin/asset/Clases/ConexionMySQL.php";

//Validar que exista sesión
if (!isset($_SESSION["iduser"])) {
    header("Location: /");
    exit;
}

//Crear conexión
$conn = new HelperMySql(
    $array_principal["server"],
    $array_principal["user"],
    $array_principal["pass"],
    $array_principal["db"]
);

//Consultar password_changed_at actual
$sql = "SELECT password_changed_at FROM Cseguridad WHERE _id_cliente = '{$_SESSION["iduser"]}' LIMIT 1";

$result = $conn->query($sql);
$user = $conn->fetch($result);

//Si no existe usuario o cambió contraseña → cerrar sesión
if (!$user || $_SESSION["password_changed_at"] !== $user["password_changed_at"]) {
    session_unset();
    session_destroy();
    header("Location: /");
    exit;
}