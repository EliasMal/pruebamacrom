<?php
    session_name("loginUsuario");
    session_start();
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    require_once "../../../../Clases/Funciones.php";
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
                        case 'Categorias': $this->jsonData["data"] = $this->getCategorias(); $this->jsonData["Bandera"] = 1; break;
                        case 'Marcas': $this->jsonData["data"] = $this->getMarcas(); $this->jsonData["Bandera"] = 1; break;
                        case 'Compatibilidad': $this->jsonData["data"] = $this->getCompatibilidad(); $this->jsonData["Bandera"] = 1; break;
                        case 'Actividad': $this->jsonData["data"] = $this->getActividad(); $this->jsonData["Bandera"] = 1; break;
                        case 'Vehiculos': $this->jsonData["data"] = $this->getModelos(); $this->jsonData["Bandera"] = 1; break;
                        case 'Modelos': $this->jsonData["data"] = $this->getAnios(); $this->jsonData["Bandera"] = 1; break;
                        case 'Refacciones':
                                $arrayLikes = $this->getexplode($this->formulario["buscar"]);
                                $this->jsonData["data"]["refacciones"] = $this->getRefaccion($arrayLikes,$this->formulario["skip"],$this->formulario["limit"]);
                                $this->jsonData["data"]["totalrefacciones"] = $this->getTotalRefacciones($arrayLikes);
                                $this->jsonData["Bandera"] = 1;
                            break;
                        case 'Refaccion':
                                $this->jsonData["data"]["Refaccion"] = $this->getRefaccionOne();
                                $ref = $this->jsonData["data"]["Refaccion"];
                                
                                $this->jsonData["data"]["ClaveUnica"]= $this->getRefaccionClave();
                                $this->jsonData["data"]["Categorias"] = $this->getCategorias();
                                $this->jsonData["data"]["Marcas"] = $this->getMarcas();
                                $this->jsonData["data"]["Proveedores"] = $this->getProveedores();
                                $this->jsonData["data"]["Compatibilidad"] = $this->getCompatibilidad();
                                $this->jsonData["data"]["Actividad"] = $this->getActividad();

                                $this->formulario["_idMarca"] = $ref["_idMarca"];
                                $this->jsonData["data"]["ListaVehiculos"] = $this->getModelos(); 
                                
                                $this->formulario["_idVehiculo"] = $ref["Modelo"];
                                $this->jsonData["data"]["ListaAnios"] = $this->getAnios();

                                $this->jsonData["Bandera"] = 1;
                            break;

                        case 'proveedores':
                                $this->jsonData["data"] = $this->getProveedores();
                                $this->jsonData["Bandera"] = 1;
                            break;

                        case 'EliminarVehiculo':
                                if($this->EliminarComp()){
                                    $id_imagen = $this->formulario['id_imagen'];
                                    $sqlProd = "SELECT Clave, Producto FROM Producto WHERE _id = '$id_imagen'";
                                    $rowProd = $this->conn->fetch($this->conn->query($sqlProd));
                                    $prodName = $rowProd ? addslashes($rowProd['Producto']) : 'Producto Desconocido';
                                    $prodClave = $rowProd ? $rowProd['Clave'] : 'S/C';

                                    $detalle = "<b>$prodName</b> (Clave: $prodClave) <br> <small class='text-danger'>Eliminó un vehículo de las compatibilidades.</small>";
                                    Funciones::guardarBitacora($this->conn, 'Refacciones', 'ELIMINAR_COMPATIBILIDAD', $detalle);
                                    
                                    $usr = $_SESSION["nombre"] ?? 'Usuario';
                                    $sqlAct = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) 
                                               VALUES ('$id_imagen', '$usr', 'Eliminó un vehículo de las compatibilidades.', '".date("Y-m-d H:i:s")."')";
                                    $this->conn->query($sqlAct);

                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "Vehículo eliminado";
                                }
                            break;
                        case 'AgregarVehiculo':
                                $resultadoSQL = $this->AgregarVehiculo();
                                if($resultadoSQL === "OK"){
                                    $this->jsonData["Bandera"] = 1;
                                    $this->jsonData["mensaje"] = "Vehículo agregado correctamente";
                                } else {
                                    $this->jsonData["Bandera"] = 0;
                                    $this->jsonData["mensaje"] = $resultadoSQL;
                                }
                            break;
                    }
                    break;
                    
                case 'delete':
                        $idBorrado = $this->formulario["id"];
                        $sqlProd = "SELECT Clave, Producto FROM Producto WHERE _id = '$idBorrado'";
                        $rowProd = $this->conn->fetch($this->conn->query($sqlProd));
                        $prodName = $rowProd ? addslashes($rowProd['Producto']) : 'Producto Desconocido';
                        $clave = $rowProd ? $rowProd['Clave'] : ($this->formulario["Clave"] ?? "S/C");

                    if($this->deleteRefaccionCompleta()){
                        $usr = $_SESSION["nombre"] ?? 'Usuario';
                        
                        $det = "<b>$prodName</b> (Clave: $clave) <br> <small class='text-danger'>Refacción eliminada permanentemente del sistema.</small>";
                        Funciones::guardarBitacora($this->conn, 'Refacciones', 'ELIMINAR_REFACCION', $det);
                        
                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Refacción eliminada correctamente";
                    } else {
                        $this->jsonData["Bandera"] = 0;
                        $this->jsonData["mensaje"] = "No se pudo eliminar la refacción de la BD. Verifica dependencias.";
                    }
                    break;

                case 'new':
                case 'edit':
                    $id = $this->setRefaccion();
                    if($id){
                        $this->formulario["lastid"] = $this->formulario["opc"]=="edit" ? $this->formulario["_id"] : $this->conn->last_id();
                        if(count($this->foto)!=0){ $this->subirImagen(); }
                        
                        $clave = $this->formulario["Clave"] ?? "S/C";
                        $usr = $_SESSION["nombre"] ?? 'Usuario';
                        $nombreProducto = addslashes($this->formulario["Producto"] ?? 'Producto Desconocido');

                        if($this->formulario["opc"] == "new"){
                            $det = "<b>$nombreProducto</b> (Clave: $clave)";
                            Funciones::guardarBitacora($this->conn, 'Refacciones', 'CREAR_REFACCION', $det);
                            $sqlAct = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) 
                                       VALUES ('{$this->formulario["lastid"]}', '$usr', 'Registro inicial de la pieza.', '".date("Y-m-d H:i:s")."')";
                            $this->conn->query($sqlAct);

                        } else {
                            $diffs = ($this->formulario["diferencias"] != "{}" && !empty($this->formulario["diferencias"])) ? $this->formulario["diferencias"] : "Modificación general";
                            $detBitacora = "<b>$nombreProducto</b> (Clave: $clave) <br> <small class='text-muted'>$diffs</small>";

                            Funciones::guardarBitacora($this->conn, 'Refacciones', 'EDITAR_REFACCION', $detBitacora);

                            $sqlAct = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) 
                                       VALUES ('{$this->formulario['_id']}', '$usr', '$diffs', '".date("Y-m-d H:i:s")."')";
                            $this->conn->query($sqlAct);
                        }

                        $this->jsonData["Bandera"] = 1;
                        $this->jsonData["mensaje"] = "Guardado con éxito";
                    } else {
                         $this->jsonData["Bandera"] = 0;
                         $this->jsonData["mensaje"] = "Error al guardar";
                    }
                    break;
            }
            $rolActual = $_SESSION["rol"] ?? '';
            $misPermisosActuales = $_SESSION["permisos"] ?? [];
            $this->jsonData["puede_publicar"] = ($rolActual === 'root' || $rolActual === 'Admin' || in_array('publicar_refacciones', $misPermisosActuales));
            
            $this->jsonData["dominio"]=$this->url;
            print json_encode($this->jsonData);
        }

        private function AgregarVehiculo() {
            $f = $this->formulario;
            $clave = $f['clave'] ?? '';
            $idmarca = $f['idmarca'] ?? '';
            $idmodelo = $f['idmodelo'] ?? '';
            $generacion = $f['generacion'] ?? '';
            $ainicial = $f['ainicial'] ?? '';
            $afinal = $f['afinal'] ?? '';
            $motor = $f['motor'] ?? '';
            $transmision = $f['transmision'] ?? '';
            $especificaciones = $f['especificaciones'] ?? '';
            $id_imagen = $f['id_imagen'] ?? '';

            $sql = "INSERT INTO compatibilidad (clave, idmarca, idmodelo, generacion, ainicial, afinal, motor, transmision, especificaciones, id_imagen) 
                    VALUES ('$clave', '$idmarca', '$idmodelo', '$generacion', '$ainicial', '$afinal', '$motor', '$transmision', '$especificaciones', '$id_imagen')";
            
            if ($this->conn->query($sql)) {
                $sqlProd = "SELECT Clave, Producto FROM Producto WHERE _id = '$id_imagen'";
                $rowProd = $this->conn->fetch($this->conn->query($sqlProd));
                $prodName = $rowProd ? addslashes($rowProd['Producto']) : 'Producto Desconocido';
                $prodClave = $rowProd ? $rowProd['Clave'] : 'S/C';

                $detalle = "<b>$prodName</b> (Clave: $prodClave) <br> <small class='text-muted'>Agregó vehículo compatible: $generacion $ainicial-$afinal</small>";
                Funciones::guardarBitacora($this->conn, 'Refacciones', 'NUEVA_COMPATIBILIDAD', $detalle);

                $sqlAct = "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) 
                           VALUES ('$id_imagen', '{$_SESSION["nombre"]}', 'Agregó vehículo compatible: $generacion $ainicial-$afinal', '".date("Y-m-d H:i:s")."')";
                $this->conn->query($sqlAct);

                return "OK";
            }
            return "CONSULTA RECHAZADA: " . $sql; 
        }

        private function deleteRefaccionCompleta() {
            $rol = $_SESSION["rol"] ?? '';
            if ($rol !== 'root' && $rol !== 'Admin') {
                return false;
            }

            $id = $this->formulario["id"];
            $sqlGaleria = "SELECT _id FROM galeriarefacciones WHERE id_producto = '$id'";
            $resGaleria = $this->conn->query($sqlGaleria);
            
            if($resGaleria) {
                while($rowGal = $this->conn->fetch($resGaleria)){
                    $idImagenSecundaria = $rowGal["_id"];
                    $rutaGaleria = "../../../../../../images/galeria/$idImagenSecundaria.webp";
                    if(file_exists($rutaGaleria)){ @unlink($rutaGaleria); }
                }
                $this->conn->query("DELETE FROM galeriarefacciones WHERE id_producto = '$id'");
            }
            
            $this->conn->query("DELETE FROM compatibilidad WHERE id_imagen = '$id'");
            $this->conn->query("DELETE FROM actividad WHERE clavepr = '$id'");
            
            $sql = "DELETE FROM Producto WHERE _id = '$id'";
            $res = $this->conn->query($sql);
            
            if($res) {
                $rutaImgPrincipal = "../../../../../../images/refacciones/$id.webp";
                if(file_exists($rutaImgPrincipal)){ @unlink($rutaImgPrincipal); }
                $rutaImgPrincipalPng = "../../../../../../images/refacciones/$id.png";
                if(file_exists($rutaImgPrincipalPng)){ @unlink($rutaImgPrincipalPng); }
            }
            
            return $res;
        }

        private function EliminarComp() {
            $sql = "DELETE FROM compatibilidad WHERE idcompatibilidad = '{$this->formulario["idcompatibilidad"]}'";
            return $this->conn->query($sql);
        }

        private function getCategorias(){
            $array = array();
            $sql = "SELECT * FROM Categorias WHERE Status = 1 ORDER BY Categoria";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){ array_push($array, $row); }
            return $array;
        }

        private function getMarcas(){
            $array = array();
            $sql = "SELECT * FROM Marcas WHERE Estatus = 1 ORDER BY Marca";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){ array_push($array, $row); }
            return $array;
        }

        private function getCompatibilidad(){
            $array = array();
            $sql = "SELECT comp.*, M.Marca, V.Modelo FROM compatibilidad as comp 
            inner join Marcas as M on (M._id = comp.idmarca)
            inner join Modelos as V on (V._id = comp.idmodelo) where id_imagen='{$this->formulario["id"]}' order by idcompatibilidad";
            $res = $this->conn->query($sql);
            while($row= $this->conn->fetch($res)){ array_push($array, $row); }
            return $array;
        }

        private function getActividad(){
            $array = array();
            $sql = "SELECT * FROM actividad where clavepr='{$this->formulario["id"]}' order by fecha_modificacion desc";
            $res = $this->conn->query($sql);
            while($row= $this->conn->fetch($res)){ array_push($array, $row); }
            return $array;
        }

        private function getModelos(){
            $array = array();
            $filtro = isset($this->formulario["_idMarca"]) ? "AND _idMarca = ".$this->formulario["_idMarca"] : "";

            $sql = "SELECT * FROM Modelos WHERE Estatus = 1 $filtro ORDER BY Modelo";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){ array_push($array, $row); }
            return $array;
        }

        private function getAnios(){
            $array = array();
            if(isset($this->formulario["_idVehiculo"]) && !empty($this->formulario["_idVehiculo"]) && $this->formulario["_idVehiculo"] != 'undefined') {
                $filtro = "WHERE _idModelo = " . $this->formulario["_idVehiculo"];
            } else {
                $filtro = ""; 
            }

            $sql = "SELECT _id, Anio FROM Anios $filtro";
            $res = $this->conn->query($sql);
            
            if($res) {
                while($row = $this->conn->fetch($res)){ 
                    array_push($array, $row); 
                }
            }
            return $array;
        }

        private function getRefaccion($arrayLikes, $skip=0, $limit=20){
            $this->formulario["historico"] = $this->formulario["historico"]=="false"? 1:0;
            $this->formulario["publicados"] = $this->formulario["publicados"]=="true"? 1:0;
            $array = array();
            $sql = "SELECT P._id, P.Clave, P.Producto, C.Categoria, M.Marca, V.Modelo, A.Anio, P.Precio1, P.Precio2, P.No_parte, P.Estatus, P.RefaccionNueva, P.RefaccionOferta, P.Enviogratis, P.stock, P.precio_manual
                    FROM Producto as P
                    INNER JOIN Categorias as C ON (C._id = P._idCategoria)
                    INNER JOIN Marcas as M ON (M._id = P._idMarca)
                    INNER JOIN Modelos as V ON (V._id = P.Modelo)
                    INNER JOIN Anios as A ON (A._id = P.Anios)
                    WHERE ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']})
                    AND P.Estatus = {$this->formulario["historico"]} AND Publicar = {$this->formulario["publicados"]} 
                    ORDER BY {$this->formulario["orden"]} {$this->formulario["ordentype"]} LIMIT $skip, $limit";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){
                $row["imagen"] = file_exists("../../../../../../images/refacciones/{$row["_id"]}.png");
                $row["RefaccionNueva"] = $row["RefaccionNueva"]==1? true: false;
                $row["RefaccionOferta"] = $row["RefaccionOferta"]==1? true:false;
                $row["Enviogratis"] = $row["Enviogratis"]==1? true:false;
                array_push($array, $row);
            }
            return $array;
        }

        private function getTotalRefacciones($arrayLikes){
            $sql = "SELECT count(*) as total FROM Producto as P WHERE ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']}) AND P.Estatus = {$this->formulario["historico"]} AND Publicar = {$this->formulario["publicados"]}";
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["total"];
        }

        private function getexplode($string){
            $arrayLikes = array("Productos"=>"(", "Clave"=>"(");
            $array = explode(" ", $string);
            $limitarray = count($array);
            foreach ($array as $key => $value) {
                if($key < ($limitarray-1)){
                    $arrayLikes["Productos"] .= "P.Producto LIKE '%$value%' AND ";
                    $arrayLikes["Clave"] .= "P.Clave LIKE '%$value%' AND ";
                }else{
                    $arrayLikes["Productos"] .= "P.Producto LIKE '%$value%') ";
                    $arrayLikes["Clave"] .= "P.Clave LIKE '%$value%')";
                }
            }
            return $arrayLikes;
        }

        private function getRefaccionOne(){
            $sql = "SELECT P.* FROM Producto as P WHERE P._id = '{$this->formulario["id"]}'";
            $row = $this->conn->fetch($this->conn->query($sql));
            $row["imagen"] = file_exists("../../../../../../images/refacciones/{$row["_id"]}.png");
            $row["precio_manual"] = $row["precio_manual"]==1? TRUE:FALSE;
            $row["Estatus"] = $row["Estatus"]==1? TRUE:FALSE;
            $row["RefaccionNueva"] = $row["RefaccionNueva"]==1? TRUE:FALSE;
            $row["RefaccionOferta"] = $row["RefaccionOferta"]==1? TRUE:FALSE;
            $row["Enviogratis"] = $row["Enviogratis"]==1? TRUE:FALSE;
            $row["Publicar"] = $row["Publicar"]==1? TRUE:FALSE;
            $row["Kit"] = $row["Kit"]==1? TRUE:FALSE;
            $row["RefaccionLiquidacion"] = (isset($row["RefaccionLiquidacion"]) && $row["RefaccionLiquidacion"] == 1) ? TRUE : FALSE;
            
            return $row;
        }

        private function getRefaccionClave(){
            $sql = "SELECT P.* FROM Producto as P WHERE P.Clave = '{$this->formulario["id"]}'";
            return $this->conn->fetch($this->conn->query($sql));
        }

        private function getProveedores(){
            $array = array();
            $sql = "SELECT _id, Proveedor FROM Proveedor WHERE Estatus = 1";
            $res = $this->conn->query($sql);
            while($row = $this->conn->fetch($res)){ array_push($array,$row); }
            return $array;
        }
        
        private function setRefaccion() {
            $f = $this->formulario;
            $rol = $_SESSION["rol"] ?? '';
            $misPermisos = $_SESSION["permisos"] ?? [];
            $puedePublicar = ($rol === 'root' || $rol === 'Admin' || in_array('publicar_refacciones', $misPermisos));

            $estatus = (isset($f["Estatus"]) && ($f["Estatus"] === "true" || $f["Estatus"] === "1")) ? 1 : 0;
            $precio_manual = (isset($f["precio_manual"]) && ($f["precio_manual"] === "true" || $f["precio_manual"] === "1")) ? 1 : 0;
            $liquidacion = (isset($f["RefaccionLiquidacion"]) && ($f["RefaccionLiquidacion"] === "true" || $f["RefaccionLiquidacion"] === "1")) ? 1 : 0;
            $nueva = (isset($f["RefaccionNueva"]) && ($f["RefaccionNueva"] === "true" || $f["RefaccionNueva"] === "1")) ? 1 : 0;
            $oferta = (isset($f["RefaccionOferta"]) && ($f["RefaccionOferta"] === "true" || $f["RefaccionOferta"] === "1")) ? 1 : 0;
            $envio = (isset($f["Enviogratis"]) && ($f["Enviogratis"] === "true" || $f["Enviogratis"] === "1")) ? 1 : 0;
            $kit = (isset($f["Kit"]) && ($f["Kit"] === "true" || $f["Kit"] === "1")) ? 1 : 0;
            
            $fechaActual = date("Y-m-d H:i:s");
            $usuarioActual = isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : 'Usuario';
                        
            if ($f["opc"] == "new") {
                $publicar = $puedePublicar ? ((isset($f["Publicar"]) && ($f["Publicar"] === "true" || $f["Publicar"] === "1")) ? 1 : 0) : 0;

                $sql = "INSERT INTO Producto (Clave, Producto, No_parte, _idCategoria, _idMarca, Modelo, Anios, id_proveedor, Precio1, Precio2, Estatus, Publicar, precio_manual, Descripcion, stock, Alto, Largo, Ancho, Peso, RefaccionLiquidacion, RefaccionNueva, RefaccionOferta, Enviogratis, Kit, userCreated, dateCreated, userModify, dateModify) 
                        VALUES ('{$f["Clave"]}', '{$f["Producto"]}', '{$f["No_parte"]}', '{$f["_idCategoria"]}', '{$f["_idMarca"]}', '{$f["Modelo"]}', '{$f["Anios"]}', '{$f["id_proveedor"]}', '{$f["Precio1"]}', '{$f["Precio2"]}', $estatus, $publicar, $precio_manual, '{$f["Descripcion"]}', '{$f["stock"]}', '{$f["Alto"]}', '{$f["Largo"]}', '{$f["Ancho"]}', '{$f["Peso"]}', $liquidacion, $nueva, $oferta, $envio, $kit, '$usuarioActual', '$fechaActual', '$usuarioActual', '$fechaActual')";
            } else {
                
                if ($puedePublicar) {
                    $publicar = (isset($f["Publicar"]) && ($f["Publicar"] === "true" || $f["Publicar"] === "1")) ? 1 : 0;
                    $fragmentoPublicar = "Publicar = $publicar,";
                } else {
                    $fragmentoPublicar = "";
                }

                $sql = "UPDATE Producto SET 
                        Clave = '{$f["Clave"]}', 
                        Producto = '{$f["Producto"]}', 
                        No_parte = '{$f["No_parte"]}', 
                        _idCategoria = '{$f["_idCategoria"]}', 
                        _idMarca = '{$f["_idMarca"]}', 
                        Modelo = '{$f["Modelo"]}', 
                        Anios = '{$f["Anios"]}', 
                        id_proveedor = '{$f["id_proveedor"]}',
                        Precio1 = '{$f["Precio1"]}', 
                        Precio2 = '{$f["Precio2"]}', 
                        Estatus = $estatus, 
                        $fragmentoPublicar
                        precio_manual = $precio_manual, 
                        RefaccionLiquidacion = $liquidacion, 
                        RefaccionNueva = $nueva,
                        RefaccionOferta = $oferta,
                        Enviogratis = $envio,
                        Kit = $kit,
                        Descripcion = '{$f["Descripcion"]}', 
                        stock = '{$f["stock"]}',
                        Alto = '{$f["Alto"]}', 
                        Largo = '{$f["Largo"]}', 
                        Ancho = '{$f["Ancho"]}', 
                        Peso = '{$f["Peso"]}',
                        userModify = '$usuarioActual',
                        dateModify = '$fechaActual'
                        WHERE _id = '{$f["_id"]}'";
            }
                        
            return $this->conn->query($sql);
        }

        private function subirImagen(){
            if($this->foto["file"]["name"]!="" && $this->foto["file"]["size"]!=0){
                $subdir ="../../../../../../"; $dir = "images/refacciones/";
                $archivo = $this->formulario["lastid"].".webp";
                if(!is_dir($subdir.$dir)){ mkdir($subdir.$dir,0755); }
                move_uploaded_file($this->foto["file"]["tmp_name"], $subdir.$dir.$archivo);
            }
        }
    }
    
    $app = new Refacciones($array_principal);
    $app->principal();
?>