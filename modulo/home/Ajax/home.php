<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of home
 *
 * @author francisco
 */
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

                            if($_SESSION["iduser"] == null){
                                
                                $_SESSION["iduser"] = $this->dataLogin["_id_cliente"];
                                $_SESSION["Cenvio"] = $this->getCenvio();
                                $_SESSION["cupon"] = "macrupon";
                                $_SESSION["acreditacion"] = $this->dataLogin["cuponacre"];
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
           
        }
        print json_encode($this->jsonData);
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

    private function getUser(){
        $sql = "SELECT _id_cliente FROM Cseguridad where username='{$_SESSION["usr"]}'";
        $this->dataLogin = $this->conn->fetch($this->conn->query($sql));
        
        return count($this->dataLogin)!=0? true:false;
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
        $sql = "SELECT _clienteid, Clave, No_parte, Cantidad, Precio, Producto as _producto, Alto, Largo, Ancho, Peso, imagenid, Existencias FROM Carrito where _clienteid='{$_SESSION["iduser"]}'";
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            array_push($array, $row);
        }
        return $array;
    }

    private function getmasVendidos (){
        $sql = "select PROV._id as idProveedor, DP._idProducto as _id, P.Clave, P.Producto, P._idMarca, P.color, 
        M.Marca, P.Precio1, P.Enviogratis from DetallesPedidos as DP
        inner join Producto as P on (P._id = DP._idProducto) 
        left join Proveedor as PROV on (P.id_proveedor = PROV._id)
        inner join Marcas as M on (P._idMarca = M._id)
        where P.Estatus = 1
        group by _idProducto order by rand() limit 8";
        return $this->conn->fetch_all($this->conn->query($sql));
    }

    private function getnuevosProductos (){
        $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1, 
                P.RefaccionNueva, P.Enviogratis from Producto as P
                left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                inner join Marcas as M on (P._idMarca = M._id)
                where P.RefaccionNueva=1 and P.Estatus = 1
                order by rand() limit 8";
        return $this->conn->fetch_all($this->conn->query($sql));
    }

    private function getProductosOferta(){
        $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio2, 
                P.RefaccionOferta, P.Enviogratis from Producto as P
                left join Proveedor as PROV on (P.id_proveedor = PROV._id)
                inner join Marcas as M on (P._idMarca = M._id)
                where P.RefaccionOferta=1 and P.Estatus = 1
                order by rand() limit 8";
        return $this->conn->fetch_all($this->conn->query($sql));
    }

    private function getProductosliquidacion(){
        $sql = "SELECT PROV._id as idProveedor, P._id, P.Clave, P.Producto, P._idMarca, P.color, M.Marca, P.Precio1,
            P.RefaccionLiquidacion, P.Enviogratis from Producto as P
            left join Proveedor as PROV on (P.id_proveedor = PROV._id)
            inner join Marcas as M on (P._idMarca = M._id)
            where P.RefaccionLiquidacion=1 and P.Estatus = 1
            order by rand() limit 8";
        return $this->conn->fetch_all($this->conn->query($sql));
    }

    private function getImageProductos ($array = array()){
        foreach ($array as $key => $value) {
            $array[$key]["Enviogratis"] = $array[$key]["Enviogratis"] == 1? true: false;
            $array[$key]["imagen"] = file_exists("../../../images/refacciones/{$value["_id"]}.png");
            $array[$key]["imagenproveedor"] = $value["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$value["idProveedor"]}.png"):false;
        }
        return $array;
    }
     
    private function getRefacciones($x=0, $y = 21 ){
        $array = array();
            
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and P._idCategoria = {$this->formulario["categoria"]}":"";
            }
        
            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    if(isset($this->formulario["marca"]) and isset($this->formulario["vehiculo"]) and (isset($this->formulario["anio"]) and strlen($this->formulario["anio"])!=0)){
                        if(isset($this->formulario["marca"]) and isset($this->formulario["vehiculo"]) and isset($this->formulario["anio"]) and (isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0)){
                                $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]} and P.Anios = {$this->formulario["anio"]}"; 
                        }else{
                           $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]} and P.Anios = {$this->formulario["anio"]}"; 
                        }
                    }else{
                       $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]}"; 
                    }
                }else{
                    $condicion .= " and P._idMarca = {$this->formulario["marca"]} ";
                    
                }
            }
        
            
        $sql = "SELECT P.*, PROV._id as idProveedor, PROV.tag_alt as tag_altproveedor, PROV.tag_title as tag_titleproveedor FROM Producto AS P "
        . "left join Proveedor as PROV on (P.id_proveedor = PROV._id) "
        ."where P.Estatus = 1 and (P.Producto like '%{$this->formulario["producto"]}%' "
        . "or P.No_parte like '%{$this->formulario["producto"]}%') $condicion order by P.Producto LIMIT $x, $y";
            
        $id = $this->conn->query($sql);
        while ($row = $this->conn->fetch($id)){
            $row["imagen"] = file_exists("../../../images/refacciones/{$row["_id"]}.png");
            $row["imagenproveedor"] = $row["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$row["idProveedor"]}.png"):false;
            array_push($array, $row);
        }
        return $array;
    }
    
    private function getTrefacciones(){
        if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
            $condicion = $this->formulario["categoria"]!= "T"? " and P._idCategoria = {$this->formulario["categoria"]}":"";
        }
    
        if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
            if(isset($this->formulario["marca"]) and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                if(isset($this->formulario["marca"]) and isset($this->formulario["vehiculo"]) and (isset($this->formulario["anio"]) and strlen($this->formulario["anio"])!=0)){
                    if(isset($this->formulario["marca"]) and isset($this->formulario["vehiculo"]) and isset($this->formulario["anio"]) and (isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0)){
                            $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]} and P.Anios = {$this->formulario["anio"]}"; 
                    }else{
                       $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]} and P.Anios = {$this->formulario["anio"]}"; 
                    }
                }else{
                   $condicion .= " and P._idMarca = {$this->formulario["marca"]} and P.Modelo = {$this->formulario["vehiculo"]}"; 
                }
            }else{
                $condicion .= " and P._idMarca = {$this->formulario["marca"]} ";
                
            }
        }
    
        
        $sql = "SELECT count(*) as Trefacciones FROM Producto AS P "
        . "left join Proveedor as PROV on (P.id_proveedor = PROV._id) "
        ."where P.Estatus = 1 and (P.Producto like '%{$this->formulario["producto"]}%' "
        . "or P.No_parte like '%{$this->formulario["producto"]}%') $condicion order by P.Producto ";
        
        $row = $this->conn->fetch($this->conn->query($sql));
        return $row["Trefacciones"];
    }

    private function getOneRefaccion(){
        $sql = "select P._id, P.Clave, P.Producto, C.Categoria, M.Marca, P.Precio1, P.Precio2,
            P.No_parte, P.Descripcion, V.Modelo, A.Anio, P.RefaccionNueva, P.RefaccionOferta,
            P.Alto, P.Ancho, P.Largo, P.Peso
            from Producto as P 
            inner join Categorias as C on (C._id = P._idcategoria)
            inner join Marcas as M on (M._id = P._idMarca)
            inner join Modelos as V on (V._id = P.Modelo)
            inner join Anios as A on (A._id = P.Anios)
            where P._id = {$this->formulario["id"]}";
        $row = $this->conn->fetch($this->conn->query($sql));
        $row["imagen"] = file_exists("../../../images/refacciones/{$row["_id"]}.png");
        return $row;
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
        