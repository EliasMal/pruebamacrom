<?php
    session_name("loginCliente");
    session_start();   
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";    
    date_default_timezone_set('America/Mexico_City');

    class Catalogo{
        private $conn;
        private $jsonData = array("Bandera"=>false, "Mensaje"=>"", "Data"=>array());

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);    
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        public function principal(){
            $this->methodo = $_SERVER['REQUEST_METHOD'];
            switch($this->methodo){
                case 'GET':
                    $this->formulario = array_map("htmlspecialchars", $_GET);
                    switch($this->formulario["opc"]){
                        case 'Buscar':
                            switch($this->formulario["tipo"]){
                                case 'Categorias':
                                    $this->jsonData["Bandera"] = true;
                                    $this->jsonData["Mensaje"] = "Entro a la seccion de categorias";
                                    $this->jsonData["Data"]["Categorias"] = $this->getCategorias();
                                    $this->jsonData["Data"]["Marcas"] = $this->getMarcas();
                                   
                                break;
                                case 'Vehiculos':
                                    $this->jsonData["Bandera"] = true;
                                    $this->jsonData["Mensaje"] = "Entro a la seccion de categorias";
                                    $this->jsonData["Data"]["Vehiculos"] = $this->getModelos();
                                break;
                                case 'Modelos':
                                    $this->jsonData["Bandera"] = true;
                                    $this->jsonData["Data"]["Modelos"] = $this->getAnios();
                                break;
                                case 'Anios':
                                case 'Refaccion':
                                case 'Paginacion':
                                    $this->jsonData["Bandera"] = true;
                                break;
                            }
                            $buscarlikes = $this->getexplode($this->formulario["producto"]);
                            $this->jsonData["Data"]["Trefacciones"] = $this->getTrefacciones($buscarlikes);
                            $this->jsonData["Data"]["Refacciones"] = $this->getRefacciones($buscarlikes, $this->formulario["x"],$this->formulario["y"]);
                        break;
                        case 'OneRefaccion':
                            $this->jsonData["Data"]["Refaccion"] = $this->getOneRefaccion();
                            $this->jsonData["Data"]["Galeria"] = $this->getGeleria($this->formulario["id"]);
                            $this->jsonData["Data"]["Productos"] = $this->getProductos($this->jsonData["Data"]["Refaccion"]);
                            $this->jsonData["Data"]["Compatibilidad"] = $this->getCompatibilidad($this->formulario["id"]);
                            $this->jsonData["Bandera"] = true;
                        break;
                    }
                break;
            }
            print json_encode($this->jsonData);
        }
        
        private function getCompatibilidad($id){
            $array = array();
            $sql = "SELECT * FROM compatibilidad as comp 
            inner join Marcas as M on (M._id = comp.idmarca)
            inner join Modelos as V on (V._id = comp.idmodelo) where id_imagen = $id order by idcompatibilidad";
            $di = $this->conn->query($sql);
            while($row= $this->conn->fetch($di)){
                array_push($array, $row);
            }
            return $array;
        }
        private function getCategorias (){
            $sql = "SELECT _id, Categoria FROM Categorias where status = 1 order by Categoria";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getMarcas (){
            $sql = "SELECT _id, Marca FROM Marcas where Estatus = 1 order by Marca";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getModelos(){
            $sql = "Select _id, Modelo from Modelos where Estatus = 1 and _idMarca = "
                    .$this->formulario["marca"] ." order by Modelo asc";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getAnios(){
            $sql = "Select _id, Anio from Anios where _idModelo= ".$this->formulario["vehiculo"]. " order by Anio asc";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getexplode($string){
            $arrayLikes = array("Productos"=>"(", "Clave"=>"(");
            $array = explode(" ",$string);
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

        private function getTrefacciones($arrayLikes){
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
            . "where P.Estatus = 1 and P.Publicar = 1 and ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']})  "
            . "$condicion order by P.Producto ";
            
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["Trefacciones"];
        }

        private function getRefacciones($arrayLikes, $x=0, $y = 20 ){
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
            ."where P.Estatus = 1 and P.Publicar = 1 and ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']}) "
            . "$condicion order by P.dateCreated DESC LIMIT $x, $y";
                
            $id = $this->conn->query($sql);
            while ($row = $this->conn->fetch($id)){
                $row["imagen"] = file_exists("../../../images/refacciones/{$row["_id"]}.png");
                $row["imagenproveedor"] = $row["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$row["idProveedor"]}.png"):false;
                $row["Enviogratis"] = $row["Enviogratis"] == 1? true: false;
                $row["RefaccionOferta"] = $row["RefaccionOferta"] == 1? true: false;
                array_push($array, $row);
            }
            return $array;
        }

        private function getOneRefaccion(){
            $sql = "select P._id, P.Clave, P.Producto, C.Categoria, M.Marca, P.Precio1, P.Precio2,
                P.No_parte, P.Descripcion, V.Modelo, A.Anio, P.RefaccionNueva, P.RefaccionOferta,
                P.Alto, P.Ancho, P.Largo, P.Peso, P._idCategoria, P._idMarca, P.Anios, P.Modelo as _idModelo,
                P.Enviogratis
                from Producto as P 
                inner join Categorias as C on (C._id = P._idCategoria)
                inner join Marcas as M on (M._id = P._idMarca)
                inner join Modelos as V on (V._id = P.Modelo)
                inner join Anios as A on (A._id = P.Anios)
                where P._id = {$this->formulario["id"]}";
            $row = $this->conn->fetch($this->conn->query($sql));
            $row["imagen"] = file_exists("../../../images/refacciones/{$row["_id"]}.png");
            $row["Enviogratis"] = $row["Enviogratis"]==1? true: false;
            $row["RefaccionOferta"] = $row["RefaccionOferta"] == 1? true: false;
            return $row;
        }

        private function getGeleria ($id){
            $array = array();
            $sql = "SELECT _id, tag_alt, tag_title FROM galeriarefacciones where id_producto = $id";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProductos($data){
            $array = array();
            $sql = "SELECT P.*, PROV._id as idProveedor FROM Producto as P "
                    ."left join Proveedor as PROV on (P.id_proveedor = PROV._id)"
                    ."where P.Modelo = {$data['_idModelo']} and P._idMarca = {$data['_idMarca']} "
                    ."and P.Anios = {$data['Anios']} and P.Estatus = 1 ORDER BY rand() LIMIT 20";
            $id = $this->conn->query($sql);
            while($row = $this->conn->fetch($id)){
                $row["imagen"] = file_exists("../../../images/refacciones/{$row["_id"]}.png");
                $row["imagenproveedor"] = $row["idProveedor"]!= null? file_exists("../../../images/Marcasrefacciones/{$row["idProveedor"]}.png"):false;
                $row["Enviogratis"] = $row["Enviogratis"]==1? true: false;
                array_push($array,$row);
            }
            return $array;
        }

    }
    
$app = new Catalogo($array_principal);
$app->principal();