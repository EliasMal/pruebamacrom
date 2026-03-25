<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_name("loginUsuario");
session_start();

require_once "../../Clases/dbconectar.php";
require_once "../../Clases/ConexionMySQL.php";
require_once "../../Clases/Funciones.php";

date_default_timezone_set('America/Mexico_City');

class getRefacciones {

    private $conn;
    private $jsonData = ["Bandera"=>0,"mensaje"=>"","Data"=>[]];
    private $xml;
    private $formulario;
    private $fechaActual;
    private $horaActual;

    public function __construct($config){
        $this->conn = new HelperMySql(
            $config["server"],
            $config["user"],
            $config["pass"],
            $config["db"]
        );
        $this->fechaActual = date("Y-m-d");
        $this->horaActual = date("H:i:s");
    }

    public function main(){

        $json = json_decode(file_get_contents("php://input"), true);
        $this->formulario = (object) $json;

        try {
            switch($this->formulario->opc ?? ''){
                case "get":
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Data"] = $this->getBitacora();
                break;

                case "inicio_sync":
                    $this->registrarBitacoraCentral('INICIAR_SINCRONIZACION', 'Inició el proceso de sincronización con SEICOM.');
                    $this->jsonData["Bandera"] = 1;
                break;

                case "set":
                    $usr = $this->formulario->usr ?? "desconocido";

                    if(isset($this->formulario->json)){
                        $this->xml = json_decode($this->formulario->json, true);
                    } else {
                        $rawXml = $this->getUrlRefa();
                        $this->xml = $this->parseXml($rawXml);
                    }

                    $this->conn->query("START TRANSACTION");

                    $this->createTempTable();
                    $this->bulkInsertTemp();
                    $updated = $this->bulkUpdateProductos();

                    $this->setBitacoraLocal($usr, $updated);
                    $this->registrarBitacoraCentral('SINCRONIZACION_COMPLETADA', "Sincronización finalizada exitosamente: $updated productos actualizados.");

                    $this->conn->query("COMMIT");

                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Se sincronizaron $updated productos correctamente.";
                    $this->jsonData["Data"] = $this->getBitacora();
                break;

                default:
                    throw new Exception("Operación no válida.");
            }

        } catch(Exception $e){
            $this->conn->query("ROLLBACK");
            $this->registrarBitacoraCentral('ERROR_SINCRONIZACION', "Falló la sincronización SEICOM. Error: " . $e->getMessage());
            $this->jsonData["mensaje"] = $e->getMessage();
        }

        echo json_encode($this->jsonData);
    }

    private function getUrlRefa(){
        $ch = curl_init('https://volks.dyndns.info:444/service.asmx/datos_art');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        if(curl_errno($ch)){ throw new Exception("Error cURL: ".curl_error($ch)); }
        curl_close($ch);
        return $response;
    }

    private function parseXml($xmlString){
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        if($xml === false){ throw new Exception("XML inválido."); }
        return json_decode(json_encode($xml), true);
    }

    private function createTempTable(){
        $sql = "CREATE TEMPORARY TABLE tmp_refacciones ( Clave INT PRIMARY KEY, Precio DECIMAL(10,2), Stock INT) ENGINE=MEMORY";
        $this->conn->query($sql);
    }

    private function bulkInsertTemp(){
        if(!isset($this->xml["Table"])){ throw new Exception("No hay datos."); }

        $values = [];
        foreach($this->xml["Table"] as $item){
            $clave = intval($item["id_articulo"]);
            $precio = bcdiv(floatval($item["precio_5"]) * 1.16, '1', 2);
            $stock = intval($item["existencia"]);
            $values[] = "($clave,$precio,$stock)";
        }

        if(empty($values)){ throw new Exception("Sin registros."); }

        $sql = "INSERT INTO tmp_refacciones (Clave, Precio, Stock) VALUES " . implode(",", $values);
        $this->conn->query($sql);
    }

    private function bulkUpdateProductos(){
        $sql = "
        UPDATE Producto p
        INNER JOIN tmp_refacciones t ON p.Clave = t.Clave
        SET 
            p.Precio1 = IF(COALESCE(p.precio_manual, 0) = 1, p.Precio1, t.Precio), 
            p.stock = t.Stock
        WHERE p.Kit = 0";

        $this->conn->query($sql);
        return $this->conn->affected_rows();
    }

    private function setBitacoraLocal($usr, $cantidad){
        $mensaje = "Actualización de precios ($cantidad productos)";
        $sql1 = "INSERT INTO logActualizacion (mensaje, usr, fecha, hora) VALUES ('$mensaje','$usr','{$this->fechaActual}','{$this->horaActual}')";
        $this->conn->query($sql1);
    }

    private function registrarBitacoraCentral($accion, $detalles) {
        $id_usuario = $_SESSION["id_usuario"] ?? $_SESSION["id"] ?? 0; 
        $username = $_SESSION["nombre_usuario"] ?? $_SESSION["usr"] ?? 'Sistema'; 
        
        $modulo = 'Sincronizador_SEICOM';
        $ip_usuario = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; 
        
        $detalles_limpios = addslashes($detalles);
        $fechaHora = "{$this->fechaActual} {$this->horaActual}";

        $sql = "INSERT INTO Bitacora_Auditoria 
                (id_usuario, username, modulo, accion, detalles, fecha, ip_usuario) 
                VALUES 
                ($id_usuario, '$username', '$modulo', '$accion', '$detalles_limpios', '$fechaHora', '$ip_usuario')";
        
        $this->conn->query($sql);
    }

    private function getBitacora(){
        $array = [];
        $sql = "SELECT l.*, 
                COALESCE(CONCAT_WS(' ', u.Nombre, u.ApPaterno, u.ApMaterno), l.usr) as nombreCompleto 
                FROM logActualizacion l
                LEFT JOIN Usuarios u ON l.usr = u.Username
                ORDER BY l.fecha DESC, l.hora DESC";
        
        $res = $this->conn->query($sql);
        while($row = $this->conn->fetch($res)){
            $array[] = $row;
        }
        return $array;
    }
}

$app = new getRefacciones($array_principal);
$app->main();
?>