<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Blog {
    private $conn;
    private $jsonData = array("Bandera"=>false, "mensaje"=>"", "Data" => array());
    private $formulario = array();
    private $foto = array();
    private $fecha;
    private $url;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->fecha = date("Y-m-d H:i:s");
        $this->url = preg_replace("#(admin\.)?#i", "", $_SERVER["HTTP_ORIGIN"] ?? '');
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function main() {
        $this->formulario = array_map("addslashes", $_POST);
        $this->foto = isset($_FILES) ? $_FILES : array();
        
        $opc = $this->formulario["opc"] ?? '';

        switch($opc){
            case 'new':
                if($this->subirImagen()){
                    if($this->setBlog('new')){
                        $this->jsonData["Bandera"] = true;
                        $this->jsonData["mensaje"] = "Entrada de Blog publicada correctamente.";
                        
                        $titulo = $this->formulario["Titulo"] ?? $this->formulario["Title"] ?? 'Sin título';
                        $this->setBitacora("NUEVA_ENTRADA", "Creó una nueva entrada de blog: '$titulo'");
                    } else {
                        $this->jsonData["Bandera"] = false;
                        $this->jsonData["mensaje"] = "Error al intentar guardar los datos en la base.";
                    }
                } else {
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error al intentar subir la imagen al servidor.";
                }
                break;

            case 'get':
                $skip = (int)($this->formulario["skip"] ?? 0);
                $limit = (int)($this->formulario["limit"] ?? 10);
                $search = $this->formulario["search"] ?? '';
                
                $this->jsonData["Bandera"] = true;
                $this->jsonData["Data"]["NoRegistrados"] = $this->getNoEntradas($search);
                $this->jsonData["Data"]["Registros"] = $this->getEntradas($skip, $limit, $search);
                break;

            case 'getOne':
                $this->jsonData["Bandera"] = true;
                $this->jsonData["Data"] = $this->getOneEntrada($this->formulario["id"]);
                break;

            case 'delete':
                $id = (int)($this->formulario["id"] ?? 0);
                
                $titulo = "ID: $id";
                $sqlT = "SELECT Title FROM Blog WHERE _id = $id LIMIT 1";
                $resT = $this->conn->query($sqlT);
                if ($rowT = $this->conn->fetch($resT)) {
                    $titulo = html_entity_decode(stripslashes($rowT['Title']), ENT_QUOTES, 'UTF-8');
                }
                
                if($this->setBlog('delete', $id)){
                    $this->jsonData["Bandera"] = true;
                    $this->jsonData["mensaje"] = "La entrada ha sido eliminada (dada de baja).";
                    
                    $this->setBitacora("ELIMINAR_ENTRADA", "Dio de baja la entrada: '$titulo'");
                    
                    $skip = (int)($this->formulario["skip"] ?? 0);
                    $limit = (int)($this->formulario["limit"] ?? 10);
                    $this->jsonData["Data"]["NoRegistrados"] = $this->getNoEntradas();
                    $this->jsonData["Data"]["Registros"] = $this->getEntradas($skip, $limit);
                } else {
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error al intentar deshabilitar el blog.";
                }
                break;

            case 'save':
                $id = (int)($this->formulario["_id"] ?? $this->formulario["id"]);
                $titulo = $this->formulario["Titulo"] ?? $this->formulario["Title"] ?? "ID: $id";
                
                if($this->setBlog('save', $id)){
                    if(count($this->foto) > 0){
                        $this->subirImagen();
                    }
                    $this->jsonData["Bandera"] = true;
                    $this->jsonData["mensaje"] = "Los cambios del Blog se han guardado.";
                    
                    $this->setBitacora("EDITAR_ENTRADA", "Editó la información de la entrada: '$titulo'");
                } else {
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error al intentar actualizar la entrada.";
                }
                break;

            case 'togglePublicar':
                $id = (int)($this->formulario["id"] ?? 0);
                $nuevoEstado = (int)($this->formulario["estado"] ?? 0);
                
                $titulo = "ID: $id";
                $sqlT = "SELECT Title FROM Blog WHERE _id = $id LIMIT 1";
                $resT = $this->conn->query($sqlT);
                if ($rowT = $this->conn->fetch($resT)) {
                    $titulo = html_entity_decode(stripslashes($rowT['Title']), ENT_QUOTES, 'UTF-8');
                }
                
                $sql = "UPDATE Blog SET Publicar = $nuevoEstado WHERE _id = $id";
                if($this->conn->query($sql)){
                    $this->jsonData["Bandera"] = true;
                    $this->jsonData["mensaje"] = $nuevoEstado ? "La entrada ahora es visible para el público." : "La entrada se ocultó como borrador.";
                    
                    $estadoStr = $nuevoEstado ? "Hizo pública" : "Ocultó (borrador)";
                    
                    $this->setBitacora("VISIBILIDAD_BLOG", "$estadoStr la entrada: '$titulo'");
                } else {
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error al intentar cambiar la visibilidad.";
                }
                break;
        }

        $this->jsonData["dominio"] = $this->url;
        header('Content-Type: application/json');
        print json_encode($this->jsonData);
    }

    private function setBitacora($accion, $detalles) {
        $id_usuario = $_SESSION["id_usuario"] ?? $_SESSION["id"] ?? 0; 
        $username = $_SESSION["nombre_usuario"] ?? $_SESSION["usr"] ?? 'Desarrollador'; 
        $modulo = 'Blog';
        $ip_usuario = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $detalles_limpios = addslashes($detalles);

        $sql = "INSERT INTO Bitacora_Auditoria 
                (id_usuario, username, modulo, accion, detalles, fecha, ip_usuario) 
                VALUES 
                ($id_usuario, '$username', '$modulo', '$accion', '$detalles_limpios', '{$this->fecha}', '$ip_usuario')";
        $this->conn->query($sql);
    }

    private function getNoEntradas($search = "") {
        $filtro = $search != "" ? "AND Title LIKE '%$search%'" : "";
        $sql = "SELECT _id FROM Blog WHERE Estatus = 1 $filtro";
        $this->conn->query($sql);
        return $this->conn->count_rows();   
    }

    private function getEntradas($skip = 0, $limit = 10, $search = "") {
        $array = array();
        $filtro = $search != "" ? "AND Title LIKE '%$search%'" : "";
        
        $sql = "SELECT _id, Title, Contenido, DATE(Fecha) as FechaCorta, TIME(Fecha) as HoraCorta,
                Imagen, Publicar, Estatus 
                FROM Blog 
                WHERE Estatus = 1 $filtro 
                ORDER BY Fecha DESC 
                LIMIT $skip, $limit";
                
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            $row["Title"] = html_entity_decode(stripslashes($row["Title"]), ENT_QUOTES, 'UTF-8');
            $row["Estatus"] = $row["Estatus"] == 1 ? true : false;
            $row["ClassEstatus"] = $row["Estatus"] ? 'bg-success' : 'bg-danger';
            $row["Publicar"] = $row["Publicar"] == 1 ? true : false;
            $row["ClassPublicar"] = $row["Publicar"] ? 'bg-success' : 'bg-secondary';
            $row["faPublicar"] = $row["Publicar"] ? 'fa-eye' : 'fa-eye-slash';
            array_push($array, $row);
        }
        return $array;   
    }

    private function getOneEntrada($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM Blog WHERE _id = $id";
        $row = $this->conn->fetch($this->conn->query($sql));
        if($row) {
            $row["Title"] = stripslashes($row["Title"]);
            $row["Contenido"] = stripslashes($row["Contenido"]);
            $row["Estatus"] = $row["Estatus"] == 1 ? true : false;
            $row["Publicar"] = $row["Publicar"] == 1 ? true : false;
        }
        return $row;
    }

    private function setBlog($opc, $id = null) {
        $publicar = ($this->formulario["Publicar"] == "true" || $this->formulario["Publicar"] == "1") ? 1 : 0;
        $estatus = ($this->formulario["Estatus"] == "true" || $this->formulario["Estatus"] == "1") ? 1 : 0;
        
        $titulo = $this->formulario["Titulo"] ?? $this->formulario["Title"] ?? '';
        $contenido = $this->formulario["Contenido"] ?? '';
        $imgCintillo = $this->formulario["filename1"] ?? $this->formulario["Imagen"] ?? '';
        $imgMiniatura = $this->formulario["filename2"] ?? $this->formulario["imagendestacada"] ?? '';

        switch($opc){
            case 'new':
                $sql = "INSERT INTO Blog (Title, Contenido, Imagen, Publicar, Estatus, imagendestacada) 
                        VALUES ('$titulo', '$contenido', '$imgCintillo', $publicar, $estatus, '$imgMiniatura')";
                break;
            case 'delete':
                $sql = "UPDATE Blog SET Estatus = 0 WHERE _id = $id"; 
                break;
            case 'save':
                $sql = "UPDATE Blog SET 
                        Title = '$titulo', 
                        Contenido = '$contenido', 
                        Publicar = $publicar, 
                        Estatus = $estatus, 
                        Imagen = '$imgCintillo', 
                        imagendestacada = '$imgMiniatura' 
                        WHERE _id = $id";
                break;
        }
        return $this->conn->query($sql) ? true : false;
    }

    private function subirImagen() {
        $exito = true;
        foreach ($this->foto as $key => $value) {
            if($value["name"] != "" && $value["size"] != 0){
                $subdir = "../../../../../../"; 
                $dir = "images/Blog/";
                $archivo = $value["name"];
                
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir, 0755, true);
                }
                if(!move_uploaded_file($value["tmp_name"], $subdir.$dir.$archivo)){
                    $exito = false;
                }
            }
        }
        return $exito;
    }
}

$app = new Blog($array_principal);
$app->main();
?>