<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
require_once "../../../../Clases/Funciones.php";
date_default_timezone_set('America/Mexico_City');

class Clientes {
    
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    
    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    
    public function main(){
        $this->formulario = json_decode(file_get_contents('php://input'));
         
        switch ($this->formulario->cliente->opc){
            case 'get':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getClientes();
                break;
            case 'new':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getnewClientes($this->getFirstWeekDay());
                break;
            case 'set':
                $id_cli = (int)$this->formulario->cliente->id;
                $nombreCliente = $this->getNombreCliente($id_cli);
                
                $this->setClientes($this->getIdCseguridad());
                $estatusTxt = (int)$this->formulario->cliente->estatus == 1 ? 'REACTIVÓ' : 'DESACTIVÓ';
                Funciones::guardarBitacora($this->conn, 'Clientes', 'ESTATUS_CLIENTE', "$estatusTxt al cliente: '$nombreCliente'");
                
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getClientes();
                break;
            case 'pass':
                $id_cli = (int)$this->formulario->cliente->id;
                $nombreCliente = $this->getNombreCliente($id_cli);
                
                $pass = $this->create_password();
                $this->setPass($pass, $this->getIdCseguridad());
                
                Funciones::guardarBitacora($this->conn, 'Clientes', 'CAMBIO_PASSWORD', "Generó nueva contraseña automática para el cliente: '$nombreCliente'");
                
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["pass"] = $pass;
                break;
            case 'perfil':
                $this->jsonData["Bandera"] = 1;
                $element = $this->getOneCliente();
                $element["avisoprivacidad"] = $element["avisoprivacidad"]==0 ? false : true;
                $this->jsonData["data"] = $element;
                $this->jsonData["count"] = $this->getCount();
                break;
            case 'updatePerfil':
                if($this->updatePerfilCliente()) {
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Perfil actualizado exitosamente.";
                } else {
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["mensaje"] = "Hubo un error al actualizar los datos.";
                }
                break;
            case 'asignarCupon':
                if($this->asignarCuponCliente()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Cupón asignado al cliente.";
                }
                break;
            case 'quitarCupon':
                if($this->quitarCuponCliente()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Cupón removido del cliente.";
                }
                break;
            case 'getCuponesCliente':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["disponibles"] = $this->getCuponesDisponibles();
                $this->jsonData["cliente"] = $this->getCuponesDelCliente();
                break;
            case 'crearCupon':
                if($this->crearCupon()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Cupón creado exitosamente.";
                }
                break;
            case 'eliminarCupon':
                if($this->eliminarCupon()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["mensaje"] = "Cupón eliminado permanentemente.";
                }
                break;
            case 'toggleActivoCupon':
                if($this->toggleActivoCupon()){
                    $this->jsonData["Bandera"] = 1;
                }
                break;
            case 'toggleGlobalCupon':
                if($this->toggleGlobalCupon()){
                    $this->jsonData["Bandera"] = 1;
                }
                break;
            case 'listarCuponesAdmin':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["cupones"] = $this->listarCuponesAdmin();
                break;
        }
        print json_encode($this->jsonData);
    }

    private function getNombreCliente($id) {
        $sql = "SELECT CONCAT(nombres, ' ', Apellidos) as nombreCompleto FROM clientes WHERE _id = $id LIMIT 1";
        $res = $this->conn->query($sql);
        if ($res && $row = $this->conn->fetch($res)) {
            $nombre = trim($row['nombreCompleto']);
            if (!empty($nombre)) return html_entity_decode(stripslashes($nombre), ENT_QUOTES, 'UTF-8');
        }
        return "ID: $id";
    }

    private function getCodigoCupon($id) {
        $sql = "SELECT codigo FROM cupones WHERE id = $id LIMIT 1";
        $res = $this->conn->query($sql);
        if ($res && $row = $this->conn->fetch($res)) {
            return stripslashes($row['codigo']);
        }
        return "ID: $id";
    }

    private function crearCupon(){
        $codigo = strtoupper(trim($this->formulario->cliente->codigo));
        $descuento = (int)$this->formulario->cliente->descuento;
        $uso_unico = (int)$this->formulario->cliente->uso_unico;
        $fecha = $this->formulario->cliente->fecha_expiracion ?: "NULL";
        $es_global = (int)$this->formulario->cliente->es_global;

        if($fecha != "NULL") $fecha = "'$fecha'";

        $sql = "INSERT INTO cupones (codigo, descuento, uso_unico, fecha_expiracion, activo, creado_en, es_global)
                VALUES ('$codigo', $descuento, $uso_unico, $fecha, 1, NOW(), $es_global)";
        
        // BITÁCORA
        Funciones::guardarBitacora($this->conn, 'Clientes', 'CREAR_CUPON', "Creó el cupón '$codigo' con $descuento% de descuento.");
        
        return $this->conn->query($sql);
    }

    private function updatePerfilCliente(){
        $c = $this->formulario->cliente;
        $id_cliente = (int)($c->_id ?? $c->id);

        $nombres = addslashes($c->nombres ?? '');
        $apellidos = addslashes($c->Apellidos ?? '');
        $correo = addslashes($c->correo ?? '');
        $telefono = addslashes($c->telefono ?? '');

        $sql = "UPDATE clientes SET nombres = '$nombres', Apellidos = '$apellidos', correo = '$correo', telefono = '$telefono'
                WHERE _id = $id_cliente";
                
        // BITÁCORA
        $nombreCompleto = trim(stripslashes($nombres) . " " . stripslashes($apellidos));
        Funciones::guardarBitacora($this->conn, 'Clientes', 'EDITAR_PERFIL', "Actualizó los datos personales del cliente: '$nombreCompleto'");

        return $this->conn->query($sql);
    }

    private function eliminarCupon(){
        $id = (int)$this->formulario->cliente->id;
        $codigoCupon = $this->getCodigoCupon($id);
        
        // BITÁCORA 
        Funciones::guardarBitacora($this->conn, 'Clientes', 'ELIMINAR_CUPON', "Eliminó permanentemente el cupón: '$codigoCupon'");
        
        $sql = "DELETE FROM cupones WHERE id = $id";
        return $this->conn->query($sql);
    }

    private function toggleActivoCupon(){
        $id = (int)$this->formulario->cliente->id;
        $codigoCupon = $this->getCodigoCupon($id); 
        
        $sql = "UPDATE cupones SET activo = IF(activo = 1, 0, 1) WHERE id = $id";
        
        // BITÁCORA
        Funciones::guardarBitacora($this->conn, 'Clientes', 'ESTATUS_CUPON', "Cambió el estado activo/inactivo del cupón: '$codigoCupon'");
        
        return $this->conn->query($sql);
    }

    private function toggleGlobalCupon(){
        $id = (int)$this->formulario->cliente->id;
        $codigoCupon = $this->getCodigoCupon($id); 

        $sql = "UPDATE cupones SET es_global = IF(es_global = 1, 0, 1) WHERE id = $id";
        
        // BITÁCORA
        Funciones::guardarBitacora($this->conn, 'Clientes', 'ALCANCE_CUPON', "Cambió el tipo (Global/Específico) del cupón: '$codigoCupon'");
        
        return $this->conn->query($sql);
    }

    private function asignarCuponCliente(){
        $idCliente = (int)$this->formulario->cliente->id;
        $idCupon   = (int)$this->formulario->cliente->id_cupon;
        
        $nombreCliente = $this->getNombreCliente($idCliente);
        $codigoCupon = $this->getCodigoCupon($idCupon);
        
        // BITÁCORA
        Funciones::guardarBitacora($this->conn, 'Clientes', 'ASIGNAR_CUPON', "Asignó el cupón '$codigoCupon' al cliente: '$nombreCliente'");
        
        $sql = "INSERT IGNORE INTO clientes_cupones (id_cliente, id_cupon) VALUES ($idCliente, $idCupon)";
        return $this->conn->query($sql);
    }

    private function quitarCuponCliente(){
        $idCliente = (int)$this->formulario->cliente->id;
        $idCupon   = (int)$this->formulario->cliente->id_cupon;
        
        $nombreCliente = $this->getNombreCliente($idCliente);
        $codigoCupon = $this->getCodigoCupon($idCupon);
        
        // BITÁCORA
        Funciones::guardarBitacora($this->conn, 'Clientes', 'REMOVER_CUPON', "Removió el cupón '$codigoCupon' del cliente: '$nombreCliente'");
        
        $sql = "DELETE FROM clientes_cupones WHERE id_cliente = $idCliente AND id_cupon = $idCupon";
        return $this->conn->query($sql);
    }


    private function listarCuponesAdmin(){
        $array = array();
        $sql = "SELECT id, codigo, descuento, uso_unico, fecha_expiracion, activo, creado_en, es_global
                FROM cupones ORDER BY creado_en DESC";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)) array_push($array, $row);
        return $array;
    }

    private function getClientes(){
        $array = array();
        $historico_val = $this->formulario->cliente->historico;
        $historico = ($historico_val === true || $historico_val === "true" || $historico_val == 1) ? 0 : 1;
        
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
                C.correo, C.telefono, Cs.Estatus  
                from clientes as C 
                inner join Cseguridad as Cs on (Cs._id_cliente = C._id) 
                where Cs.Estatus = $historico order by C.Apellidos, C.nombres";
        
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)) array_push($array, $row);
        return $array;
    }

    private function getnewClientes($week_start){
        $array = array();
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
                C.correo, C.telefono, Cs.Estatus  
                from clientes as C 
                inner join Cseguridad as Cs on (Cs._id_cliente = C._id) 
                where Cs.Estatus = 1 and C.FechaCreacion >= '$week_start' 
                order by C.Apellidos, C.nombres";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)) array_push($array, $row);
        return $array;
    }

    private function getFirstWeekDay(){
        return (date("D")=="Mon") ? date("Y-m-d") : date("Y-m-d", strtotime("last Monday"));
    }

    private function getOneCliente(){
        $sql = "Select * from clientes as C inner join Cseguridad as Cs on (Cs._id_cliente = C._id)
                where C._id = ". (int)$this->formulario->cliente->id;
        return $this->conn->fetch($this->conn->query($sql));
    }
    
    private function getCount(){
       $sql = "SELECT COUNT(_idPedidos) as NPedidos from Pedidos where (Acreditado = 5 or Acreditado = 1) 
               and _idCliente = ".(int)$this->formulario->cliente->id;
       return $this->conn->fetch($this->conn->query($sql));
    }
    
    private function getIdCseguridad(){
        $sql = "select _id from Cseguridad where _id_cliente = ".(int)$this->formulario->cliente->id;
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["_id"];
    }

    private function setClientes($idCseguridad){
        $estatus = (int)$this->formulario->cliente->estatus;
        $id = (int)$this->formulario->cliente->id;
        $this->conn->query("update Cseguridad set Estatus = $estatus where _id = $idCseguridad");
        $this->conn->query("update clientes set Estatus = $estatus where _id= $id");
        return true;
    }
    
    private function setPass ($pass, $id){
        $sql = "update Cseguridad set password = SHA('$pass') where _id = $id";
        return $this->conn->query($sql);    
    }

    private function create_password(){
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $pass = "";
        for($i=1 ; $i<=10 ; $i++) $pass .= substr($cadena, rand(0, strlen($cadena)-1), 1);
        return $pass;
    }

    private function getCuponesDisponibles(){
        $array = array();
        $sql = "SELECT id, codigo, descuento FROM cupones WHERE activo = 1 ORDER BY codigo ASC";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)) array_push($array, $row);
        return $array;
    }
    
    private function getCuponesDelCliente(){
        $array = array();
        $idCliente = (int)$this->formulario->cliente->id;
        $sql = "SELECT c.id, c.codigo, c.descuento FROM clientes_cupones cc
                INNER JOIN cupones c ON c.id = cc.id_cupon
                WHERE cc.id_cliente = $idCliente AND c.activo = 1";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)) array_push($array, $row);
        return $array;
    }
}

$app = new Clientes($array_principal);
$app->main();