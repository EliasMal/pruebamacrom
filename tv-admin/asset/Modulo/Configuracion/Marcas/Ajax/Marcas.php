<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Marcas
 *
 * @author francisco
 */

    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');
    
    class Marcas {
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");
        private $formulario = array();
        private $url;
        
        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->url = preg_replace("#(admin\.)?#i","", $_SERVER["HTTP_ORIGIN"]);
        }

        public function __destruct() {
            unset($this->conn);
        }
        
        public function principal(){
            $this->formulario = array_map("htmlspecialchars", $_POST);
            $this->foto =  isset($_FILES)? $_FILES:array();
            
            switch ($this->formulario["opc"]) {
                case 'buscar':
                    $this->jsonData["Data"]["noRegistros"] = $this->getNoMarcas($this->formulario["find"]);
                    $this->jsonData["Data"]["Registros"] = $this->getMarcas($this->formulario["find"],$this->formulario["skip"],$this->formulario["limit"]);
                    $this->jsonData["Bandera"] = 1;
                    break;
                case 'edit':
                case 'new':
                case 'disabled':
                case 'enabled':
                    if($this->setMarcas()){
                        $this->formulario["lastid"] = $this->formulario["opc"]=="edit"? $this->formulario["_id"]:$this->conn->last_id();
                        /*Actualizar los colores de los productos*/
                        $this->setProductos();
                        if(count($this->foto)!=0){
                            $this->subirImagen();
                        }
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = $this->getMensajeSuccess();
                    }else{
                        $this->jsonData["mensaje"] = $this->getMensajeError();
                    }
                    break;
                
            }
            $this->jsonData["dominio"]=$this->url;
            print json_encode($this->jsonData);
        }
        
        private function getMensajeSuccess(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "La Marca ha sido Creada";
                        break;
                    case 'edit':
                        $mensaje = "La Marca ha sido Modificada";
                        break;
                    case 'disabled':
                        $mensaje = "La Marca ha sido Desactivada";
                        break;
                    case 'enabled':
                        $mensaje = "La Marca ha sido Activada";
                        break;
                 }
            return $mensaje;
        }
        
        private function getMensajeError(){
            $mensaje = "";
                 switch($this->formulario["opc"]){
                    case 'new':
                        $mensaje = "Error: La Marca no ha sido Creada";
                        break;
                    case 'edit':
                        $mensaje = "Error: La Marca no ha sido Modificada";
                        break;
                    case 'disabled':
                        $mensaje = "Error: La Marca no ha sido Desactivada";
                        break;
                    case 'enabled':
                        $mensaje = "Error: La Marca no ha sido Activada";
                        break;
                 }
            return $mensaje;
        }

        private function getNoMarcas($find=""){
            $sql = "SELECT count(*) as total FROM Marcas WHERE Estatus = {$this->formulario["historico"]} and Marca like '%$find%'";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["total"];
        }
        
        private function getMarcas($find="", $skip=0, $limit=10){
            $array = array();
            $sql = "SELECT * FROM Marcas where Estatus = {$this->formulario["historico"]} and Marca like '%$find%' Limit $skip, $limit";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                $row["foto"] = file_exists("../../../../../../images/Marcas/".$row["_id"].".png");
                array_push($array, $row);
            }
            return $array;
        }
        
        private function setMarcas(){
            $sql = "";
            switch($this->formulario["opc"]){
                case 'new':
                    $sql = "INSERT INTO Marcas(Marca, Estatus, USRCreacion, USRModificacion, FechaCreacion, FechaModificacion, Color) values "
                        . "('{$this->formulario["Marca"]}','1','{$_SESSION["usr"]}','{$_SESSION["usr"]}','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."','{$this->formulario["Color"]}')";
                    break;
                case 'edit':
                    $sql = "UPDATE Marcas SET Marca = '{$this->formulario["Marca"]}', USRModificacion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."', Color='{$this->formulario["Color"]}' "
                            . "WHERE _id = ".$this->formulario["_id"];
                    
                    break;
                case 'disabled':
                    $sql = "UPDATE Marcas SET Estatus = 0 , USRModificacion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' "
                            . "WHERE _id = ".$this->formulario["_id"]; 
                    break;
                case 'enabled':
                    $sql = "UPDATE Marcas SET Estatus = 1 , USRModificacion='{$_SESSION["usr"]}', FechaModificacion='".date("Y-m-d H:i:s")."' "
                            . "WHERE _id = ".$this->formulario["_id"]; 
                    break;
            }
            return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
        }
        
        private function setProductos(){
            $sql = "select * from Producto where _idMarca=".$this->formulario["lastid"];
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $sql = "Update Producto set Color = '{$this->formulario["Color"]}' where _id = '{$row["_id"]}'";
                $this->conn->query($sql);
            }
            return;
        }
        
        private function subirImagen(){
            //print_r($this->foto);
            if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
                $subdir ="../../../../../../"; 
                $dir = "images/Marcas/";
                $archivo = $this->formulario["lastid"].".png";
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir,0755);
                }
                if($archivo && move_uploaded_file($this->foto["file"]["tmp_name"], $subdir.$dir.$archivo)){
                    //$this->rutaimagen= $dir.$archivo;
                    return true;
                }else{
                    echo "no se subio la imagen";
                }
            }else{
                return false;
            }
        }
        
    }
    
    $app = new Marcas($array_principal);
    $app->principal();