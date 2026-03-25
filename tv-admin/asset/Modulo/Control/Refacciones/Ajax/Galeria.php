<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
require_once "../../../../Clases/Funciones.php";
date_default_timezone_set('America/Mexico_City');

class Galeria {
    private $conn;
    private $jsonData = array("Bandera"=>0,"mensaje"=>"");
    private $formulario = array();
    private $foto = array();

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
    }

    public function __destruct() {
        unset($this->conn);
    }

    public function principal(){
        $this->formulario = array_map("htmlspecialchars", $_POST);
        $this->foto = isset($_FILES) ? $_FILES : array();
        
        $opc = $this->formulario["opc"] ?? '';

        switch ($opc) {
            case "new":
            case "edit":
                $id = $this->setGaleria();
                if($id){
                    $this->formulario["lastid"] = ($opc == "edit") ? ($this->formulario["_id"] ?? 0) : $this->conn->last_id();
                    if(count($this->foto) != 0){
                        $this->subirImagen();
                        $this->Setimgactividad();
                        $this->LastMod();
                    }
                    $this->jsonData["mensaje"] = "La imagen se agregó a la galería";
                    $this->jsonData["Bandera"] = 1;
                } else {
                    $this->jsonData["mensaje"] = "Error al insertar en BD";
                }
                break;

            case 'get':
                if(isset($this->formulario["id"]) && $this->formulario["id"] > 0) {
                    $this->jsonData["Data"] = $this->getGaleria();
                    $this->jsonData["Bandera"] = 1;
                } else {
                    $this->jsonData["Data"] = array();
                    $this->jsonData["mensaje"] = "ID de producto no recibido";
                }
                break;

            case 'erase':
                if($this->EraseImagen()){
                    if($this->setGaleria()){
                        $this->LastMod();
                        $this->Delimgactividad();
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Imagen eliminada correctamente";
                    } else {
                        $this->jsonData["mensaje"] = "Error al borrar registro de BD";
                    }
                } else {
                    $this->jsonData["mensaje"] = "Error: No se pudo eliminar el archivo físico";
                }
                break;
        }
        header('Content-Type: application/json');
        print json_encode($this->jsonData);
    }

    private function getGaleria (){
        $array = array();
        $id_prod = intval($this->formulario["id"]);
        $sql = "SELECT _id, tag_alt, tag_title FROM galeriarefacciones WHERE id_producto = $id_prod";
        $res = $this->conn->query($sql);
        while($row = $this->conn->fetch($res)){
            $row["imagen"] = file_exists("../../../../../../images/galeria/{$row["_id"]}.webp");
            array_push($array, $row);
        }
        return $array;
    }

    private function setGaleria(){
        $opc = $this->formulario["opc"];
        $usr = $_SESSION["nombre"] ?? 'Sistema';
        
        switch($opc){
            case 'new':
                $fecha = date("Y-m-d H:i:s");
                $id_ref = intval($this->formulario["id_refaccion"]);
                $sql = "INSERT INTO galeriarefacciones (tag_alt, tag_title, id_producto, USRCreacion, USRModificacion, FechaCreacion, FechaModificacion) 
                        VALUES ('{$this->formulario["tag_alt"]}', '{$this->formulario["tag_title"]}', $id_ref, '$usr', '$usr', '$fecha', '$fecha')";
                break;
            case 'erase':
                $id_borrar = intval($this->formulario["id"]);
                $sql = "DELETE FROM galeriarefacciones WHERE _id = $id_borrar"; 
                break;
            default: return false;
        }
        return $this->conn->query($sql);
    }

    private function Setimgactividad(){
        $id_ref = $this->formulario["id_refaccion"] ?? 0;
        $usr = $_SESSION["nombre"] ?? 'Usuario';
        
        $sql = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('$id_ref', '$usr', 'Agregó nueva imagen a galería.', '".date("Y-m-d H:i:s")."')";
        $this->conn->query($sql);

        $detalle = "Subió una imagen a la galería de la refacción ID: $id_ref";
        return Funciones::guardarBitacora($this->conn, 'Galeria_Refacciones', 'NUEVA_IMAGEN', $detalle);
    }

    private function Delimgactividad(){
        $id_ref = $this->formulario["id_refaccion"] ?? 0;
        $usr = $_SESSION["nombre"] ?? 'Usuario';
        $img_id = $this->formulario["id"] ?? '';
        
        $sql = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('$id_ref', '$usr', 'Eliminó imagen ID: $img_id de la galería', '".date("Y-m-d H:i:s")."')";
        $this->conn->query($sql);

        $detalle = "Eliminó la imagen ID: $img_id de la galería de la refacción ID: $id_ref";
        return Funciones::guardarBitacora($this->conn, 'Galeria_Refacciones', 'ELIMINAR_IMAGEN', $detalle);
    }

    private function LastMod(){
        $id_ref = $this->formulario["id_refaccion"] ?? 0;
        $usr = $_SESSION["nombre"] ?? 'Usuario';
        $sql = "UPDATE Producto SET userModify='$usr', dateModify='".date("Y-m-d H:i:s")."' WHERE _id = $id_ref";
        return $this->conn->query($sql);
    }
    
    private function subirImagen(){
        if(isset($this->foto["file"]) && $this->foto["file"]["size"] != 0){
            $subdir = "../../../../../../"; 
            $dir = "images/galeria/";
            $archivo = $this->formulario["lastid"].".webp";
            if(!is_dir($subdir.$dir)){ mkdir($subdir.$dir, 0755, true); }
            return move_uploaded_file($this->foto["file"]["tmp_name"], $subdir.$dir.$archivo);
        }
        return false;
    }

    private function EraseImagen(){
        $id = $this->formulario["id"] ?? 0;
        $ruta = "../../../../../../images/galeria/$id.webp";
        if(file_exists($ruta)){
            return unlink($ruta);
        }
        return true;
    }
}

$app = new Galeria($array_principal);
$app->principal();