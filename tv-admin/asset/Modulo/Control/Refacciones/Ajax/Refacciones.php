<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    date_default_timezone_set('America/Mexico_City');
    
    class Refacciones {
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
                    switch ($this->formulario["tipo"]) {
                        case 'Categorias':
                                $this->jsonData["data"] = $this->getCategorias();
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Marcas':
                                $this->jsonData["data"] = $this->getMarcas();
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Compatibilidad':
                            $this->jsonData["data"] = $this->getCompatibilidad();
                            $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Actividad':
                            $this->jsonData["data"] = $this->getActividad();
                            $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Vehiculos':
                                $this->jsonData["data"] = $this->getModelos();
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Modelos':
                                $this->jsonData["data"] = $this->getAnios();
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Refacciones':
                                $arrayLikes = $this->getexplode($this->formulario["buscar"]);
                                $this->jsonData["data"]["refacciones"] = $this->getRefaccion($arrayLikes,$this->formulario["skip"],$this->formulario["limit"]);
                                $this->jsonData["data"]["totalrefacciones"] = $this->getTotalRefacciones($arrayLikes);
                                $this->jsonData["data"]["separado"] = $this->getexplode($this->formulario["buscar"]);
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Refaccion':
                                $this->jsonData["data"]["Refaccion"] = $this->getRefaccionOne();
                                $this->jsonData["data"]["Categorias"] = $this->getCategorias();
                                $this->jsonData["data"]["Compatibilidad"] = $this->getCompatibilidad();
                                $this->jsonData["data"]["Marcas"] = $this->getMarcas();
                                $this->jsonData["data"]["Proveedores"] = $this->getProveedores();
                                $this->jsonData["data"]["Actividad"] = $this->getActividad();
                                /*Especificar la funcion que ontendra los vehiculos que le quedan a la refaccion*/

                                $this->jsonData["data"]["RVehiculo"] = array();
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'proveedores':
                                $this->jsonData["data"] = $this->getProveedores();
                                $this->jsonData["Bandera"] = 1;
                            break;

                        case 'EliminarVehiculo':
                                $this->EliminarComp();
                            break;
                    }
                    break;
                case 'new':
                case 'edit':
                    
                    
                    $id = $this->setRefaccion();
                    if($id){
                        $this->formulario["lastid"] = $this->formulario["opc"]=="edit"? $this->formulario["_id"]:$this->conn->last_id();
                        if(count($this->foto)!=0){
                            $this->subirImagen();
                        }
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "La refaccion se ha almacenado de manera satisfactoria";
                    }else{
                         $this->jsonData["Bandera"] = 0;
                         $this->jsonData["mensaje"] = $id;
                    }
                    break;
            }

            $this->jsonData["dominio"]=$this->url;
            print json_encode($this->jsonData);
        }
        
        private function EliminarComp(){
            $sql = "DELETE FROM compatibilidad where idcompatibilidad =".$this->formulario["idcompatibilidad"]." and clave =".$this->formulario["clave"];
            return $this->conn->query($sql);
        }

        private function getCategorias(){
            $array = array();
            $sql = "SELECT * FROM Categorias where Status = 1 order by Categoria";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getMarcas(){
            $array = array();
            $sql = "SELECT * FROM Marcas where Estatus = 1 order by Marca";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }

        private function getCompatibilidad(){
            $array = array();
            $sql = "SELECT * FROM compatibilidad as comp 
            inner join Marcas as M on (M._id = comp.idmarca)
            inner join Modelos as V on (V._id = comp.idmodelo) where id_imagen='{$this->formulario["id"]}' order by idcompatibilidad";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }

        private function getActividad(){
            $array = array();
            $sql = "SELECT * FROM actividad where clavepr='{$this->formulario["id"]}' order by fecha_modificacion desc";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }

        private function getModelos(){
            $array = array();
            $sql = "SELECT * FROM Modelos where Estatus = 1 and _idMarca= ".$this->formulario["_idMarca"]. " order by Modelo";
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getAnios(){
            $array = array();
            $sql = "Select _id,Anio from Anios where _idModelo= ".$this->formulario["_idVehiculo"];
            $id = $this->conn->query($sql);
            while($row= $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
        
        private function setRefaccion(){
            $sql = "";
            
            switch ($this->formulario["opc"]){
                case 'new':
                    $this->formulario["Nuevo"] = $this->formulario["Nuevo"]=="true"? 1:0;
                    $this->formulario["Oferta"] = $this->formulario["Oferta"] == "true"? 1:0;
                    $this->formulario["Estatus"] = $this->formulario["Estatus"] == "true"? 1:0;
                    $this->formulario["Enviogratis"] = $this->formulario["Enviogratis"]=="true"? 1:0;
                    $this->formulario["liquidacion"] = $this->formulario["liquidacion"]=="true"? 1:0;
                    
                    $sql = "INSERT INTO Producto (Clave, Producto, No_parte, _idCategoria,_idMarca, Modelo, Anios, Precio1, Precio2, Descripcion, RefaccionNueva, RefaccionOferta, Color, Estatus, Alto,"
                    . "Largo, Ancho, Peso, id_proveedor, tag_title, tag_alt,Enviogratis, RefaccionLiquidacion, Publicar, userCreated, userModify ) value "
                    . "('{$this->formulario["Clave"]}','{$this->formulario["refaccion"]}','{$this->formulario["noParte"]}','{$this->formulario["Categoria"]}','{$this->formulario["Marca"]}',"
                    . "'{$this->formulario["Vehiculo"]}','{$this->formulario["Modelo"]}',".bcdiv($this->formulario["Precio1"],'1',2).",".bcdiv($this->formulario["Precio2"],'2',1).",'{$this->formulario["Descripcion"]}',"
                    . "'{$this->formulario["Nuevo"]}','{$this->formulario["Oferta"]}','{$this->formulario["Color"]}',{$this->formulario["Estatus"]},{$this->formulario["Alto"]},{$this->formulario["Largo"]},"
                    . "{$this->formulario["Ancho"]},{$this->formulario["Peso"]},{$this->formulario["id_proveedor"]},'{$this->formulario["tag_title"]}','{$this->formulario["tag_alt"]}'
                    ,'{$this->formulario["Enviogratis"]}','{$this->formulario["liquidacion"]}',0,'{$_SESSION["nombre"]}','{$_SESSION["nombre"]}')";
                break;
                    
                case 'edit':
                    $this->formulario["RefaccionNueva"] = $this->formulario["RefaccionNueva"]=="true"? 1:0;
                    $this->formulario["RefaccionOferta"] = $this->formulario["RefaccionOferta"] == "true"? 1:0;
                    $this->formulario["RefaccionLiquidacion"] = $this->formulario["RefaccionLiquidacion"] == "true"? 1:0;
                    $this->formulario["Enviogratis"] = $this->formulario["Enviogratis"]=="true"? 1:0;
                    $this->formulario["Publicar"] = $this->formulario["Publicar"]=="true"? 1:0;

                    $sql = "UPDATE Producto SET Clave='{$this->formulario["Clave"]}', Producto = '{$this->formulario["Producto"]}', _idCategoria='{$this->formulario["_idCategoria"]}',"
                    . " _idMarca='{$this->formulario["_idMarca"]}', Precio1 = " . bcdiv($this->formulario["Precio1"],'1',2) . ", Precio2 = ". bcdiv($this->formulario["Precio2"],'1',2) .", No_parte = '{$this->formulario["No_parte"]}', "
                    . " Descripcion='{$this->formulario["Descripcion"]}', Modelo = '{$this->formulario["Modelo"]}', Anios = '{$this->formulario["Anios"]}', "
                    . " RefaccionNueva = '{$this->formulario["RefaccionNueva"]}', RefaccionOferta ='{$this->formulario["RefaccionOferta"]}', Color = '{$this->formulario["color"]}', "
                    . " Estatus = {$this->formulario["Estatus"]}, Alto = {$this->formulario["Alto"]}, Largo = {$this->formulario["Largo"]}, Ancho = {$this->formulario["Ancho"]}, "
                    . " Peso = {$this->formulario["Peso"]}, id_proveedor = {$this->formulario["id_proveedor"]}, tag_title='{$this->formulario["tag_title"]}'," 
                    . " tag_alt='{$this->formulario["tag_alt"]}', RefaccionLiquidacion = '{$this->formulario["RefaccionLiquidacion"]}', "
                    . " Enviogratis = '{$this->formulario["Enviogratis"]}', Publicar={$this->formulario["Publicar"]},"
                    . " userModify='{$_SESSION["nombre"]}', dateModify='".date("Y-m-d H:i:s")."'"
                    . " where _id = {$this->formulario["_id"]}";
                    if($this->formulario["diferencias"] != "{}"){
                        $this->setActividad();
                    }
                break;
            }
            return $this->conn->query($sql) or $this->jsonData["error"] = $this->conn->error;
        }

        private function setActividad(){
            $sql = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('{$this->formulario["_id"]}', '{$_SESSION["nombre"]}', '{$this->formulario["diferencias"]}', '".date("Y-m-d H:i:s")."');";
            return $this->conn->query($sql);
        }

        private function getexplode($string){
            $arrayLikes = array("Productos"=>"(", "Clave"=>"(");
            $array = explode(" ", $this->formulario["buscar"]);
            $limitarray = count($array);
            foreach ($array as $key => $value) {
                if($key < ($limitarray-1)){
                    $arrayLikes["Productos"] .= "P.Producto LIKE '%$value%' and ";
                    $arrayLikes["Clave"] .= "P.Clave LIKE '%$value%' and ";
                }else{
                    $arrayLikes["Productos"] .= "P.Producto LIKE '%$value%') ";
                    $arrayLikes["Clave"] .= "P.Clave LIKE '%$value%')";
                }
            }
            return $arrayLikes;
        }

        private function getExistenciaSEICOM($clave){
            $ch = curl_init();
            //curl_setopt_array($ch,$defaults);
            curl_setopt($ch, CURLOPT_URL,'https://volks.dyndns.info:444/service.asmx/consulta_art');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST,TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(array("articulo"=>$clave)));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $data = curl_exec($ch);
            if($errno = curl_errno($ch)) {
                $error_message = curl_strerror($errno);
                echo "cURL error ({$errno}):\n {$error_message}";
            }
            curl_close($ch);
            return $data;
        }

        private function getTotalRefacciones($arrayLikes){
            //$this->formulario["historico"] = $this->formulario["historico"]=="false"? 0:1;
            //$this->formulario["publicados"] = $this->formulario["publicados"]=="true"? 0:1;
            /**WHERE (P.Producto like '%{$this->formulario["buscar"]}%' OR P.clave like '%{$this->formulario["buscar"]}%')  */
            $sql = "SELECT count(*) as total FROM Producto as P
                    inner join Categorias as C on (C._id = P._idCategoria)
                    inner join Marcas as M on (M._id = P._idMarca)
                    inner join Modelos as V on (V._id = P.Modelo)
                    inner join Anios as A on (A._id = P.Anios)
                    WHERE ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']}) 
                    and P.Estatus = {$this->formulario["historico"]} and Publicar = {$this->formulario["publicados"]} order by P.Producto";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["total"];
        }
        
        private function getRefaccion($arrayLikes, $skip=0, $limit=20){
            $this->formulario["historico"] = $this->formulario["historico"]=="false"? 1:0;
            $this->formulario["publicados"] = $this->formulario["publicados"]=="true"? 1:0;
            
            $array = array();
            $sql = "SELECT P._id, P.Clave, P.Producto, C.Categoria, M.Marca, V.Modelo,   
                    A.Anio, P.Precio1, P.Precio2, P.No_parte, P.Estatus, P.RefaccionNueva, P.RefaccionOferta, P.RefaccionLiquidacion,
                    P.Enviogratis
                    FROM Producto as P
                    inner join Categorias as C on (C._id = P._idCategoria)
                    inner join Marcas as M on (M._id = P._idMarca)
                    inner join Modelos as V on (V._id = P.Modelo)
                    inner join Anios as A on (A._id = P.Anios)
                    WHERE ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']})
                    and P.Estatus = {$this->formulario["historico"]} and Publicar = {$this->formulario["publicados"]} order by P.Producto LIMIT $skip, $limit";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $row["imagen"] = file_exists("../../../../../../images/refacciones/{$row["_id"]}.png");
                $row["RefaccionNueva"] = $row["RefaccionNueva"]==1? true: false;
                $row["RefaccionOferta"] = $row["RefaccionOferta"]==1? true:false;
                $row["RefaccionLiquidacion"] = $row["RefaccionLiquidacion"]==1? true:false;
                $row["Enviogratis"] = $row["Enviogratis"]==1? true:false;
                //$row["Stock"] = $this->getExistenciaSEICOM($row["Clave"]);
                array_push($array, $row);
            }
            return $array;
        }
        
        private function getRefaccionOne (){
            $sql = "SELECT P.* FROM Producto as P WHERE P._id = '{$this->formulario["id"]}'";
            $row = $this->conn->fetch($this->conn->query($sql));
            $row["imagen"] = file_exists("../../../../../../images/refacciones/{$row["_id"]}.png");
            $row["Estatus"] = $row["Estatus"]==1? TRUE:FALSE;
            $row["RefaccionNueva"] = $row["RefaccionNueva"]==1? TRUE:FALSE;
            $row["RefaccionOferta"] = $row["RefaccionOferta"]==1? TRUE:FALSE;
            $row["RefaccionLiquidacion"] = $row["RefaccionLiquidacion"] == 1 ? true: false;
            $row["Enviogratis"] = $row["Enviogratis"] == 1? true: false;
            $row["Publicar"] = $row["Publicar"] == 1? true:false; 
            return $row;
        }
        
        private function getProveedores(){
            $array = array();
            $sql = "select _id, Proveedor from Proveedor where Estatus = 1";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                array_push($array,$row);
            }
            return $array;
        }

        private function subirImagen(){
            //print_r($this->foto);
            if($this->foto["file"]["name"]!="" and $this->foto["file"]["size"]!=0){
                $subdir ="../../../../../../"; 
                $dir = "images/refacciones/";
                $archivo = $this->formulario["lastid"].".webp";
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir,0755);
                }
                //echo $this->url.$dir.$archivo;
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
    
    $app = new Refacciones($array_principal);
    $app->principal();

