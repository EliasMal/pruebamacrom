<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";    
    date_default_timezone_set('America/Mexico_City');

    class home {
        private $conn;
        private $jsonData = array("Bandera"=>0,"mensaje"=>"");

        private $formulario = array();
        private $dataLogin = array();
        private $redpack;
        private $dataFacturacion = array();

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        }

        public function __destruct() {
            unset($this->conn);
        }
     
        public function principal(){
            $input = file_get_contents('php://input');
            $this->formulario = json_decode($input);

            if($this->formulario === null && !empty($_POST)) {
                 $this->formulario = json_decode(json_encode($_POST)); 
            }

            if(!isset($this->formulario->modelo) || !isset($this->formulario->modelo->opc)) {
                print json_encode($this->jsonData);
                return;
            }

            switch($this->formulario->modelo->opc){
                case 'buscar':
                    switch($this->formulario->modelo->tipo){
                        case 'Categorias':
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["Data"]["Categorias"] = $this->getCategorias();
                            $this->jsonData["Data"]["Carrito"]= $this->get_Carrito();
                            $_SESSION["CarritoPrueba"] = $this->get_Carrito();
                            
                            if(isset($this->formulario->modelo->home) && $this->formulario->modelo->home){
                                $this->jsonData["Data"]["masVendidos"] = $this->getImageProductos($this->getmasVendidos());
                                $this->jsonData["Data"]["nuevos"] = $this->getImageProductos($this->getnuevosProductos());
                                $this->jsonData["Data"]["liquidacion"] = $this->getImageProductos($this->getProductosliquidacion());
                                $this->jsonData["Data"]["oferta"] = $this->getImageProductos($this->getProductosOferta());
                                
                                if(isset($_SESSION["usr"]) && !empty($_SESSION["usr"])){
                                    if($this->getUser()){ 
                                        $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
                                        $_SESSION["Cenvio"] = $this->getCenvio();
                                        if($this->get_Facturacion()){
                                            $_SESSION["facturacion"] = $this->dataFacturacion["Predeterminado"];
                                        } else {
                                            $_SESSION["facturacion"] = 0;
                                        }
                                    }
                                }
                            }
                        break;
                    }
                break;
                case 'OneRefaccion':
                    $this->jsonData["Refaccion"] = $this->getOneRefaccion();
                    $this->jsonData["Galeria"] = $this->getGeleria($this->formulario["id"]);
                    $this->jsonData["Bandera"] = 1;
                    break;
                case 'getC':
                    $this->jsonData["Data"] = $this->getOneCostumer();
                    $this->jsonData["Bandera"] = 1;
                break;
                case 'ActExistencias':
                    $this->jsonData["Bandera"] = 1;
                    $this->ActExistencias();
                break;
                case 'ActPrecio':
                    $this->jsonData["Bandera"] = 1;
                    $this->ActPrecio();
                break;
            }
            print json_encode($this->jsonData);
        }
        
        private function ActExistencias(){

            $sqlCheck = "SELECT Kit, stock FROM Producto WHERE Clave = '{$this->formulario->modelo->refaccion}' LIMIT 1";
            $resCheck = $this->conn->query($sqlCheck);
            
            if($resCheck && $this->conn->count_rows() > 0) {
                $row = $this->conn->fetch($resCheck);
                
                if($row['Kit'] == 1 || $row['Kit'] == '1') {
                    $stockReal = $row['stock'];
                    $cant = $this->formulario->modelo->Cant;
                    if($cant > $stockReal) { $cant = $stockReal > 0 ? $stockReal : 1; }
                    
                    $sql = "UPDATE Carrito SET Existencias = '$stockReal', Cantidad = '$cant' WHERE _clienteid = '{$_SESSION["iduser"]}' AND Clave = '{$this->formulario->modelo->refaccion}'";
                    return $this->conn->query($sql);
                }
            }

            if($this->formulario->modelo->Cant > $this->formulario->modelo->NewExistencia){
                $sql = "UPDATE Carrito set Existencias = '{$this->formulario->modelo->NewExistencia}', Cantidad = '{$this->formulario->modelo->NewExistencia}'  where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
            }else{
                $sql = "UPDATE Carrito set Existencias = '{$this->formulario->modelo->NewExistencia}' where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
            }
            return $this->conn->query($sql);
        }

        private function ActPrecio(){
            $sqlCheck = "SELECT Kit, precio_manual, Precio1, Precio2 FROM Producto WHERE Clave = '{$this->formulario->modelo->refaccion}' LIMIT 1";
            $resCheck = $this->conn->query($sqlCheck);
            
            if($resCheck && $this->conn->count_rows() > 0) {
                $row = $this->conn->fetch($resCheck);
                
                if($row['Kit'] == 1 || $row['Kit'] == '1' || $row['precio_manual'] == 1 || $row['precio_manual'] == '1') {
                    $sql = "UPDATE Carrito set Precio = '{$row['Precio1']}', Precio2 = '{$row['Precio2']}' where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
                    return $this->conn->query($sql);
                }
            }

            $sql = "UPDATE Carrito set Precio = '{$this->formulario->modelo->NewPrecio}' where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
            return $this->conn->query($sql);
        }

        private function getCategorias (){
            $array = array();
            $sql = "SELECT _id, Categoria FROM Categorias where status = 1 order by Categoria";
            $id = $this->conn->query($sql);
            if($id){
                while($row = $this->conn->fetch($id)){
                    $row["logo"] = file_exists("../../../images/Categorias/{$row["_id"]}.png");
                    array_push($array, $row);
                }
            }
            return $array;
        }

        private function get_Facturacion(){
            $sql = "SELECT * FROM Facturacion where _id_cliente = '{$_SESSION["iduser"]}' and Predeterminado = 1";
            $res = $this->conn->query($sql);
            $this->dataFacturacion = $res ? $this->conn->fetch($res) : NULL;
            return $this->dataFacturacion == NULL ? false : true;
        }
    
        private function getUser(){
            $sql = "SELECT _id_cliente FROM Cseguridad where username='{$_SESSION["usr"]}'";
            $res = $this->conn->query($sql);
            $this->dataLogin = $res ? $this->conn->fetch($res) : NULL;
            return $this->dataLogin == NULL ? false : true;
        }

        private function getCenvio(){
            $array = array("Envio"=>"","costo"=>0, "Servicio"=>"") ;
            $cp = isset($this->dataLogin["Codigo_postal"]) ? $this->dataLogin["Codigo_postal"] : '';
            
            if(empty($cp)) {
                $array["Envio"] = "N";
                return $array;
            }

            $sql = "select CE.precio from Cenvios as CE 
            inner join CPmex as CP on (CP.D_mnpio = CE.Municipio)
            where CP.d_codigo = '{$cp}' group by CE.precio";
            
            $id = $this->conn->query($sql);
            if($id && $this->conn->count_rows() != 0){
                $row = $this->conn->fetch();
                $array["Envio"] = "L"; 
                $array["costo"] = floatval($row["precio"]);
                $array["Servicio"] = "METROPOLITANO";
            }else{
                $array["Envio"] = "N"; 
                $array["costo"] = 0;
            }
            return $array;
        }

        private function get_Carrito(){
            $array = array();
            if(!isset($_SESSION["iduser"])) return $array;
            
            $sql = "SELECT DISTINCT _clienteid, CR.Clave, CR.No_parte, CR.Cantidad, CR.Precio, CR.Precio2, P.RefaccionOferta, 
            CR.Producto as _producto, CR.Alto, CR.Largo, CR.Ancho, CR.Peso, CR.imagenid, CR.Existencias,
            P.Kit, P.stock as StockBD, P.precio_manual, P.Precio1 as Precio1BD, P.Precio2 as Precio2BD 
            FROM Carrito CR left JOIN Producto as P on P.Clave = CR.Clave where _clienteid='{$_SESSION["iduser"]}' and _clienteid != 0";
            
            $id = $this->conn->query($sql);
            if($id){
                while ($row = $this->conn->fetch($id)){

                    if($row['Kit'] == 1 || $row['Kit'] == '1'){
                        $stockReal = intval($row['StockBD']);
                        $row['Existencias'] = $stockReal; 
                        
                        // Validar si el cliente guardó más stock del que tienes actualmente
                        if(intval($row['Cantidad']) > $stockReal){
                            $row['Cantidad'] = $stockReal > 0 ? $stockReal : 1; 
                            $updateSql = "UPDATE Carrito SET Existencias = '$stockReal', Cantidad = '{$row['Cantidad']}' WHERE Clave = '{$row['Clave']}' AND _clienteid = '{$_SESSION["iduser"]}'";
                            $this->conn->query($updateSql);
                        }
                    }

                    if($row['Kit'] == 1 || $row['Kit'] == '1' || $row['precio_manual'] == 1 || $row['precio_manual'] == '1'){
                        $row['Precio'] = $row['Precio1BD'];
                        $row['Precio2'] = $row['Precio2BD'];
                        
                        $updateSqlPrice = "UPDATE Carrito SET Precio = '{$row['Precio1BD']}', Precio2 = '{$row['Precio2BD']}' WHERE Clave = '{$row['Clave']}' AND _clienteid = '{$_SESSION["iduser"]}'";
                        $this->conn->query($updateSqlPrice);
                    }

                    array_push($array, $row);
                }
            }
            return $array;
        }

        private function getmasVendidos (){
            $sql = "select PROV._id as idProveedor, DP._idProducto as _id, P.Clave, P.Producto, P._idMarca, P.color, 
            M.Marca, P.Precio1, P.Precio2, P.RefaccionOferta, P.Enviogratis, P.stock from DetallesPedidos as DP
            inner join Producto as P on (P._id = DP._idProducto) 
            left join Proveedor as PROV on (P.id_proveedor = PROV._id)
            inner join Marcas as M on (P._idMarca = M._id)
            where P.Estatus = 1
            group by _idProducto order by P._id DESC limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getnuevosProductos (){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1, 
                    P.RefaccionNueva, P.Enviogratis, P.stock from Producto as P
                    left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                    inner join Marcas as M on (P._idMarca = M._id)
                    where P.RefaccionNueva=1 and P.Estatus = 1
                    order by P._id DESC limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProductosOferta(){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio2, 
                    P.RefaccionOferta, P.Enviogratis, P.stock from Producto as P
                    left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                    inner join Marcas as M on (P._idMarca = M._id)
                    where P.RefaccionOferta=1 and P.Estatus = 1
                    order by P._id DESC limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProductosliquidacion(){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1,
                P.RefaccionLiquidacion, P.Enviogratis, P.stock from Producto as P
                left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                inner join Marcas as M on (P._idMarca = M._id)
                where P.RefaccionLiquidacion=1 and P.Estatus = 1
                order by P._id DESC limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getImageProductos ($array = array()){
            if(!$array) return array();
            
            foreach ($array as $key => $value) {
                $array[$key]["Enviogratis"] = $array[$key]["Enviogratis"] == 1? true: false;
                $array[$key]["RefaccionOferta"] = $array[$key]["RefaccionOferta"] == 1? true: false;
                
                if (file_exists("../../../images/refacciones/{$value["_id"]}.webp")) {
                    $array[$key]["img_ext"] = "webp";
                    $array[$key]["imagen"] = true;
                } else if (file_exists("../../../images/refacciones/{$value["_id"]}.png")) {
                    $array[$key]["img_ext"] = "png";
                    $array[$key]["imagen"] = true;
                } else {
                    $array[$key]["img_ext"] = "";
                    $array[$key]["imagen"] = false;
                }

                $array[$key]["imagenproveedor"] = $value["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$value["idProveedor"]}.png"):false;
            }
            return $array;
        }

        private function getGeleria ($id){
            $array = array();
            $sql = "SELECT _id, tag_alt, tag_title FROM galeriarefacciones where id_producto = $id";
            $id = $this->conn->query($sql);
            if($id){
                while($row = $this->conn->fetch($id)){
                    array_push($array, $row);
                }
            }
            return $array;
        }
    }

    $app = new home($array_principal);
    $app->principal();
?>