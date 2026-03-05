<?php
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";

$conn = new HelperMySql($array_principal["server"], $array_principal["user"], $array_principal["pass"], $array_principal["db"]);

$sql = "SELECT cupon_nombre FROM Cseguridad 
        WHERE cupon_nombre IS NOT NULL 
        AND cupon_nombre != ''";

$res = $conn->query($sql);

$cuponesInsertados = [];

while ($row = $conn->fetch($res)) {

    $cupones = explode(",", $row["cupon_nombre"]);

    foreach ($cupones as $cupon) {

        $cupon = trim($cupon);
        if ($cupon == "") continue;

        $descuento = 10;
        $codigo = $cupon;

        // Formato CUPON=50
        if (strpos($cupon, "=") !== false) {
            list($codigo, $valor) = explode("=", $cupon);
            $descuento = (int)$valor;
        }

        // Evitar duplicados
        if (in_array($codigo, $cuponesInsertados)) continue;

        $sqlInsert = "
            INSERT INTO cupones (codigo, descuento, uso_unico, activo)
            VALUES ('$codigo', $descuento, 0, 1)
        ";

        $conn->query($sqlInsert);
        $cuponesInsertados[] = $codigo;
    }
}

echo "Migración finalizada correctamente";
