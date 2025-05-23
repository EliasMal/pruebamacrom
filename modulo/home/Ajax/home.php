<?php
    session_name("loginCliente");
    session_start();   
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";    
    date_default_timezone_set('America/Mexico_City');

    class home {
        //put your code here
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
            //$this->formulario = array_map("htmlspecialchars", $_POST);
            $this->formulario = json_decode(file_get_contents('php://input'));
            switch($this->formulario->modelo->opc){
                case 'buscar':
                    switch($this->formulario->modelo->tipo){
                        case 'Categorias':
                            $this->jsonData["Bandera"] = 1;
                            $this->jsonData["Data"]["Categorias"] = $this->getCategorias();
                            $this->jsonData["Data"]["Carrito"]= $this->get_Carrito();
                            $_SESSION["CarritoPrueba"] = $this->get_Carrito();
                            //$this->jsonData["Data"]["Marcas"] = $this->getMarcas();
                            
                            if($this->formulario->modelo->home){
                                $this->jsonData["Data"]["masVendidos"]=$this->getImageProductos($this->getmasVendidos());
                                $this->jsonData["Data"]["nuevos"] = $this->getImageProductos($this->getnuevosProductos());
                                $this->jsonData["Data"]["liquidacion"] = $this->getImageProductos($this->getProductosliquidacion());
                                $this->jsonData["Data"]["oferta"] = $this->getImageProductos($this->getProductosOferta());
                                $this->getUser();
                                $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
                                $_SESSION["Cenvio"] = $this->getCenvio();
                                if($this->get_Facturacion()==true){
                                    $_SESSION["facturacion"] = $this->dataFacturacion["Predeterminado"];
                                }else{
                                    $_SESSION["facturacion"] = 0;
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
            $sql = "UPDATE Carrito set Existencias = '{$this->formulario->modelo->NewExistencia}' where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
            return $this->conn->query($sql);
        }

        private function ActPrecio(){
            $sql = "UPDATE Carrito set Precio = '{$this->formulario->modelo->NewPrecio}' where _clienteid = '{$_SESSION["iduser"]}' and Clave = '{$this->formulario->modelo->refaccion}'";
            return $this->conn->query($sql);
        }

        private function getCategorias (){
            $array = array();
            $sql = "SELECT _id, Categoria FROM Categorias where status = 1 order by Categoria";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $row["logo"] = file_exists("../../../images/Categorias/{$row["_id"]}.png");
                array_push($array, $row);
            }
            return $array;

        }

        private function get_Facturacion(){
            $sql = "SELECT * FROM Facturacion where _id_cliente = '{$_SESSION["iduser"]}' and Predeterminado = 1";
            $this->dataFacturacion = $this->conn->fetch($this->conn->query($sql));
            return $this->dataFacturacion == NULL? false:true;
        }
    
        private function getUser(){
            $sql = "SELECT _id_cliente FROM Cseguridad where username='{$_SESSION["usr"]}'";
            $this->dataLogin = $this->conn->fetch($this->conn->query($sql));
            return $this->dataLogin == NULL? false:true;
        }

        private function getCenvio(){
            $array = array("Envio"=>"","costo"=>0, "Servicio"=>"") ;
            $sql = "select CE.precio from Cenvios as CE 
            inner join CPmex as CP on (CP.D_mnpio = CE.Municipio)
            where CP.d_codigo = '{$this->dataLogin["Codigo_postal"]}' group by CE.precio";
            $id = $this->conn->query($sql);
            if($this->conn->count_rows() != 0){
                $row = $this->conn->fetch();
                $array["Envio"] = "L"; //Envio Local
                $array["costo"] = floatval($row["precio"]);
                $array["Servicio"] = "METROPOLITANO";
            }else{
                $array["Envio"] = "N"; //Envio nacional
                $array["costo"] = 0;
            }
            return $array;
        }

        private function get_Carrito(){
            $array = array();
            $sql = "SELECT DISTINCT _clienteid, CR.Clave, CR.No_parte, CR.Cantidad, CR.Precio, CR.Precio2, P.RefaccionOferta, 
            CR.Producto as _producto, CR.Alto, CR.Largo, CR.Ancho, CR.Peso, CR.imagenid, CR.Existencias 
            FROM Carrito CR left JOIN Producto as P on P.Clave = CR.Clave where _clienteid='{$_SESSION["iduser"]}' and _clienteid != 0";
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                array_push($array, $row);
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
            group by _idProducto order by rand() limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getnuevosProductos (){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1, 
                    P.RefaccionNueva, P.Enviogratis, P.stock from Producto as P
                    left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                    inner join Marcas as M on (P._idMarca = M._id)
                    where P.RefaccionNueva=1 and P.Estatus = 1
                    order by rand() limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProductosOferta(){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio2, 
                    P.RefaccionOferta, P.Enviogratis, P.stock from Producto as P
                    left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                    inner join Marcas as M on (P._idMarca = M._id)
                    where P.RefaccionOferta=1 and P.Estatus = 1
                    order by rand() limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProductosliquidacion(){
            $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1,
                P.RefaccionLiquidacion, P.Enviogratis, P.stock from Producto as P
                left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                inner join Marcas as M on (P._idMarca = M._id)
                where P.RefaccionLiquidacion=1 and P.Estatus = 1
                order by rand() limit 8";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getImageProductos ($array = array()){
            foreach ($array as $key => $value) {
                $array[$key]["Enviogratis"] = $array[$key]["Enviogratis"] == 1? true: false;
                $array[$key]["RefaccionOferta"] = $array[$key]["RefaccionOferta"] == 1? true: false;
                $array[$key]["imagen"] = file_exists("../../../images/refacciones/{$value["_id"]}.png");
                $array[$key]["imagenproveedor"] = $value["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$value["idProveedor"]}.png"):false;
            }
            return $array;
        }

        private function getGeleria ($id){
            $array = array();
            $sql = "SELECT _id, tag_alt, tag_title FROM galeriarefacciones where id_producto = $id";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                array_push($array, $row);
            }
            return $array;
        }
    
   
    }

    $app = new home($array_principal);
    $app->principal();
