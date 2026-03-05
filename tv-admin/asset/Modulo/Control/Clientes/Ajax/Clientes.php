<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Clientes {
    //put your code here
    
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
                $this->setClientes($this->getIdCseguridad());
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["Cliente"] = $this->getClientes();
                break;
            case 'pass':
                $pass = $this->create_password();
                $this->setPass($pass, $this->getIdCseguridad());
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["pass"] = $pass;
                break;
            case 'perfil':
                $this->jsonData["Bandera"] = 1;
                $element = $this->getOneCliente();
                $element["avisoprivacidad"] = $element["avisoprivacidad"]==0? false:true;
                $this->jsonData["data"] = $element;
                $this->jsonData["count"] = $this->getCount();
            break;
            case 'asignarCupon':
                $this->jsonData["Bandera"] = 1;
                $this->asignarCuponCliente();
            break;
            case 'quitarCupon':
                $this->jsonData["Bandera"] = 1;
                $this->quitarCuponCliente();
            break;
            case 'getCuponesCliente':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["disponibles"] = $this->getCuponesDisponibles();
                $this->jsonData["cliente"] = $this->getCuponesDelCliente();
            break;
            case 'crearCupon':
                $this->jsonData["Bandera"] = 1;
                $this->crearCupon();
            break;

            case 'eliminarCupon':
                $this->jsonData["Bandera"] = 1;
                $this->eliminarCupon();
            break;
            
            case 'toggleActivoCupon':
                $this->jsonData["Bandera"] = 1;
                $this->toggleActivoCupon();
            break;
            
            case 'toggleGlobalCupon':
                $this->jsonData["Bandera"] = 1;
                $this->toggleGlobalCupon();
            break;
            
            case 'listarCuponesAdmin':
                $this->jsonData["Bandera"] = 1;
                $this->jsonData["cupones"] = $this->listarCuponesAdmin();
            break;
        }
        print json_encode($this->jsonData);
    }

    private function crearCupon(){

        $codigo = strtoupper(trim($this->formulario->cliente->codigo));
        $descuento = (int)$this->formulario->cliente->descuento;
        $uso_unico = (int)$this->formulario->cliente->uso_unico;
        $fecha = $this->formulario->cliente->fecha_expiracion ?: "NULL";
        $es_global = (int)$this->formulario->cliente->es_global;

        if($fecha != "NULL"){
            $fecha = "'$fecha'";
        }

        $sql = "
            INSERT INTO cupones 
            (codigo, descuento, uso_unico, fecha_expiracion, activo, creado_en, es_global)
            VALUES 
            ('$codigo', $descuento, $uso_unico, $fecha, 1, NOW(), $es_global)
        ";

        return $this->conn->query($sql);
    }

    private function eliminarCupon(){

        $id = (int)$this->formulario->cliente->id;

        $sql = "DELETE FROM cupones WHERE id = $id";

        return $this->conn->query($sql);
    }

    private function toggleActivoCupon(){

        $id = (int)$this->formulario->cliente->id;

        $sql = "
            UPDATE cupones 
            SET activo = IF(activo = 1, 0, 1)
            WHERE id = $id
        ";

        return $this->conn->query($sql);
    }

    private function toggleGlobalCupon(){

        $id = (int)$this->formulario->cliente->id;

        $sql = "
            UPDATE cupones 
            SET es_global = IF(es_global = 1, 0, 1)
            WHERE id = $id
        ";

        return $this->conn->query($sql);
    }

    private function listarCuponesAdmin(){
                
        $array = array();
                
        $sql = "
            SELECT id, codigo, descuento, uso_unico, 
                   fecha_expiracion, activo, creado_en, es_global
            FROM cupones
            ORDER BY creado_en DESC
        ";
                
        $id = $this->conn->query($sql);
                
        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
                
        return $array;
    }

    private function getCuponesDisponibles(){
        $array = array();

        $sql = "
            SELECT id, codigo, descuento, uso_unico, fecha_expiracion
            FROM cupones
            WHERE activo = 1
            ORDER BY codigo ASC
        ";

        $id = $this->conn->query($sql);

        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }

        return $array;
    }
    
    private function getCuponesDelCliente(){
        $array = array();
        $idCliente = (int)$this->formulario->cliente->id;

        $sql = "
            SELECT c.id, c.codigo, c.descuento
            FROM clientes_cupones cc
            INNER JOIN cupones c ON c.id = cc.id_cupon
            WHERE cc.id_cliente = $idCliente
            AND c.activo = 1
            ORDER BY c.codigo ASC
        ";

        $id = $this->conn->query($sql);

        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }

        return $array;
    }

    private function asignarCuponCliente(){
        $idCliente = (int)$this->formulario->cliente->id;
        $idCupon   = (int)$this->formulario->cliente->id_cupon;

        $sql = "
            INSERT IGNORE INTO clientes_cupones (id_cliente, id_cupon)
            VALUES ($idCliente, $idCupon)
        ";

        return $this->conn->query($sql);
    }

    private function quitarCuponCliente(){
        $idCliente = (int)$this->formulario->cliente->id;
        $idCupon   = (int)$this->formulario->cliente->id_cupon;

        $sql = "
            DELETE FROM clientes_cupones
            WHERE id_cliente = $idCliente
            AND id_cupon = $idCupon
        ";

        return $this->conn->query($sql);
    }

    private function getClientes(){
        $array = array();
        $historico = $this->formulario->cliente->historico =="true"? 0:1;
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
        C.correo, C.telefono, Cs.Estatus  from clientes as C 
        inner join Cseguridad as Cs on (Cs._id_cliente = C._id) where Cs.Estatus = $historico order by C.Apellidos, C.nombres";
        
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function getnewClientes($week_start){
        $array = array();
        $sql = "select C._id, Cs.username, concat(C.Apellidos, ' ', C.nombres) as nombre, 
        C.correo, C.telefono, Cs.Estatus  from clientes as C 
        inner join Cseguridad as Cs on (Cs._id_cliente = C._id) where Cs.Estatus = 1 and C.FechaCreacion >= '$week_start' 
        order by C.Apellidos, C.nombres";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function getFirstWeekDay(){
        if(date("D")=="Mon"){
            $week_start = date("Y-m-d");
        }else{
            $week_start = date("Y-m-d", strtotime("last Monday", time()));
        }
        return $week_start;
    }

    private function getOneCliente(){
        $sql = "Select * from clientes as C inner join Cseguridad as Cs on (Cs._id_cliente = C._id)
         where C._id = ". $this->formulario->cliente->id;
        return $this->conn->fetch($this->conn->query($sql));
    }
    private function getCount(){
       $sql = "SELECT COUNT(_idPedidos) as NPedidos from Pedidos where (Acreditado = 5 or Acreditado = 1) and _idCliente = ".$this->formulario->cliente->id;
       return $this->conn->fetch($this->conn->query($sql));
    }
    private function getIdCseguridad(){
        $sql = "select _id from Cseguridad where _id_cliente = ".$this->formulario->cliente->id;
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["_id"];
    }

    private function setClientes($idCseguridad){
        $sql = "update Cseguridad set Estatus = {$this->formulario->cliente->estatus} where _id = {$idCseguridad}";
        $this->conn->query($sql);
        $sql = "update clientes set Estatus = {$this->formulario->cliente->estatus} where _id= {$this->formulario->cliente->id}";
        $this->conn->query($sql);
        return true;
    }
    
    private function setPass ($pass, $id){
        $sql = "update Cseguridad set password = SHA('$pass') where _id = $id";
        return $this->conn->query($sql);    
    }

    private function create_password(){
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $longitudCadena=strlen($cadena);
        $pass = "";
        $longitudPass=10;

        for($i=1 ; $i<=$longitudPass ; $i++){
            $pos=rand(0,$longitudCadena-1);
            $pass .= substr($cadena,$pos,1);
        }
        return $pass;
    }
}

$app = new Clientes($array_principal);
$app->main();