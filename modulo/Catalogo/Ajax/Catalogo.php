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
                                    $this->jsonData["Data"]["Proveedores"] = $this->getProveedores();
                                    $this->jsonData["Data"]["Existencias"] = $this->getExistencias();
                                    $this->jsonData["Data"]["Ofertas"] = $this->getOfertas();
                                    $this->jsonData["Data"]["Nuevos"] = $this->getNuevos();
                                    if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                                        $this->jsonData["Data"]["Vehiculos"] = $this->getModelos();
                                    }
                                break;
                                case 'Vehiculos':
                                    $this->jsonData["Bandera"] = true;
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

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0) and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0) ){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.id_proveedor IN({$this->formulario["proveedor"]}) and p.Modelo IN({$this->formulario["vehiculo"]})";
                }else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0)){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.id_proveedor IN({$this->formulario["proveedor"]})";
                } else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.Modelo IN({$this->formulario["vehiculo"]})";
                }else{
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]})";
                }
                
            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and p.id_proveedor IN({$this->formulario["proveedor"]})";
            }

            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and p.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and p.RefaccionOferta = 1";
            }    
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and p.RefaccionNueva = 1";
            }
            $sql = "SELECT p._idCategoria as _id, cate.Categoria, p.Estatus, COUNT(*) as cantidad_repetida
            FROM u619477378_macromau.Producto p
            inner join u619477378_macromau.Categorias cate on p._idCategoria = cate._id 
            where cate.Status = 1 and p.Estatus = 1 and p.Publicar = 1 $condicion 
            group by cate.Categoria having count(*) > 1 order by cantidad_repetida desc;";
            
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getMarcas (){
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and p._idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and p.id_proveedor IN({$this->formulario["proveedor"]})";
            }
            
            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if((isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    $condicion .= " and p.Modelo IN({$this->formulario["vehiculo"]})";
                }
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and p.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and p.RefaccionOferta = 1";
            }    
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and p.RefaccionNueva = 1";
            }

            $sql = "SELECT p._idMarca, marca.Marca, p.Estatus, COUNT(*) as cantidad_repetida
            FROM u619477378_macromau.Producto p
            inner join u619477378_macromau.Marcas marca on p._idMarca = marca._id 
            where marca.Estatus = 1 and p.Estatus = 1 and p.Publicar = 1 $condicion 
            group by marca.Marca having count(*) > 1 order by cantidad_repetida desc;";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getExistencias(){
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and _idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                
                if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){

                    $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and id_proveedor IN({$this->formulario["proveedor"]})";

                }else if((isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){

                    $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and Modelo IN({$this->formulario["vehiculo"]})";
                    
                }else{
                    $condicion .= " and _idMarca IN({$this->formulario["marca"]})";
                }

            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and id_proveedor IN({$this->formulario["proveedor"]})";
            }
            if(isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0){
                $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and Modelo IN({$this->formulario["vehiculo"]})";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and RefaccionNueva = 1";
            }

            $sql = "SELECT COUNT(*) as cantidad_repetida from u619477378_macromau.Producto where Estatus = 1 and Publicar = 1 and stock > 0 $condicion";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getOfertas(){     
            $sql = "SELECT COUNT(*) as cantidad_repetida from u619477378_macromau.Producto where Estatus = 1 and Publicar = 1 and RefaccionOferta = 1";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getNuevos(){
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and _idCategoria IN({$this->formulario["categoria"]})":"";
            }
            
            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                
                if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){

                    $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and id_proveedor IN({$this->formulario["proveedor"]})";

                }else if((isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){

                    $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and Modelo IN({$this->formulario["vehiculo"]})";
                    
                }else{
                    $condicion .= " and _idMarca IN({$this->formulario["marca"]})";
                }

            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and id_proveedor IN({$this->formulario["proveedor"]})";
            }
            if(isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0){
                $condicion .= " and _idMarca IN({$this->formulario["marca"]}) and Modelo IN({$this->formulario["vehiculo"]})";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and stock >= 1";
            }

            $sql="SELECT COUNT(*) as cantidad_repetida from u619477378_macromau.Producto where Estatus = 1 and Publicar = 1 and RefaccionNueva = 1 $condicion";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getModelos(){
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and p._idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0)){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.id_proveedor IN({$this->formulario["proveedor"]})";
                }else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0)){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.id_proveedor IN({$this->formulario["proveedor"]})";
                } else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]})";
                }
                
            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and p.id_proveedor IN({$this->formulario["proveedor"]})";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and p.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and p.RefaccionOferta = 1";
            }    
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and p.RefaccionNueva = 1";
            }

            $sql = "SELECT p.Modelo as _id, model.Modelo, p._idMarca, p.Estatus, COUNT(*) as cantidad_repetida
                FROM u619477378_macromau.Producto p
                inner join u619477378_macromau.Modelos model on p.Modelo = model._id 
                where model.Estatus = 1 and p.Estatus = 1 and Publicar = 1 $condicion  
                group by model.Modelo having count(*) > 1 order by cantidad_repetida desc;";
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getProveedores(){
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and p._idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0) ){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.Modelo IN({$this->formulario["vehiculo"]})";
                }else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]})";
                } else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]}) and p.Modelo IN({$this->formulario["vehiculo"]})";
                }else{
                    $condicion .= " and p._idMarca IN({$this->formulario["marca"]})";
                }
                
            } else if(isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0){
                $condicion .= " and p.Modelo IN({$this->formulario["vehiculo"]})";
            }

            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and p.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and p.RefaccionOferta = 1";
            }    
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and p.RefaccionNueva = 1";
            }

            $sql = "SELECT p.id_proveedor, prove.Proveedor, p.Estatus, COUNT(*) as cantidad_repetida
                FROM u619477378_macromau.Producto p
                inner join u619477378_macromau.Proveedor prove on p.id_proveedor = prove._id 
                where prove.Estatus = 1 and p.Estatus = 1 and p.Publicar = 1 $condicion 
                group by prove.Proveedor having count(*) > 1 order by cantidad_repetida desc;";
                
            return $this->conn->fetch_all($this->conn->query($sql));
        }

        private function getAnios(){
            if(str_contains($this->formulario["vehiculo"],",")){
                $sql = "Select _id, Anio from Anios where _idModelo IN(".$this->formulario["vehiculo"]. ") order by Anio asc";
                
            } else{
                $sql = "Select _id, Anio from Anios where _idModelo= ".$this->formulario["vehiculo"]. " order by Anio asc";
            }
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
                $condicion = $this->formulario["categoria"]!= "T"? " and P._idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0) and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0) ){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.id_proveedor IN({$this->formulario["proveedor"]}) and P.Modelo IN({$this->formulario["vehiculo"]})";
                }else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0)){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.id_proveedor IN({$this->formulario["proveedor"]})";
                } else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.Modelo IN({$this->formulario["vehiculo"]})";
                }else{
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]})";
                }
                
            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and P.id_proveedor IN({$this->formulario["proveedor"]})";
            }

            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and P.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and P.RefaccionOferta = 1";
            }    
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and P.RefaccionNueva = 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"gratis")!== false){
                $condicion .= " and P.Enviogratis = 1";
            }
            
           $sql = "SELECT count(*) as Trefacciones FROM Producto AS P "
            . "left join Proveedor as PROV on (P.id_proveedor = PROV._id) "
            . "where P.Estatus = 1 and P.Publicar = 1 and ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']})"
            . "$condicion order by P.Producto ";
            $this->jsonData["Mensaje"] = "sql: ".$sql;
            $row = $this->conn->fetch($this->conn->query($sql));
            return $row["Trefacciones"];
        }

        private function getRefacciones($arrayLikes, $x=0, $y = 20 ){
            $array = array();
            $orden = $this->formulario["orden"];
            $tipodeorden = $this->formulario["tipodeorden"];
            if(isset($this->formulario["categoria"]) && strlen($this->formulario["categoria"])!=0){
                $condicion = $this->formulario["categoria"]!= "T"? " and P._idCategoria IN({$this->formulario["categoria"]})":"";
            }

            if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0){
                if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0) and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0) ){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.id_proveedor IN({$this->formulario["proveedor"]}) and P.Modelo IN({$this->formulario["vehiculo"]})";
                }else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0)){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.id_proveedor IN({$this->formulario["proveedor"]})";
                } else if(isset($this->formulario["marca"]) and strlen($this->formulario["marca"])!=0 and (isset($this->formulario["vehiculo"]) and strlen($this->formulario["vehiculo"])!=0)){
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]}) and P.Modelo IN({$this->formulario["vehiculo"]})";
                }else{
                    $condicion .= " and P._idMarca IN({$this->formulario["marca"]})";
                }
                
            } else if(isset($this->formulario["proveedor"]) and strlen($this->formulario["proveedor"])!=0){
                $condicion .= " and P.id_proveedor IN({$this->formulario["proveedor"]})";
            }

            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"xistencia")!== false){
                $condicion .= " and P.stock >= 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"ferta")!== false){
                $condicion .= " and P.RefaccionOferta = 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"Articulos_Nuevos")!== false){
                $condicion .= " and P.RefaccionNueva = 1";
            }
            if(isset($this->formulario["disponibilidad"]) and strpos($this->formulario["disponibilidad"],"gratis")!== false){
                $condicion .= " and P.Enviogratis = 1";
            }
            
            $sql = "SELECT P.*, PROV._id as idProveedor, PROV.tag_alt as tag_altproveedor, PROV.tag_title as tag_titleproveedor FROM Producto AS P "
            . "left join Proveedor as PROV on (P.id_proveedor = PROV._id) "
            ."where P.Estatus = 1 and P.Publicar = 1 and ({$arrayLikes['Productos']} OR {$arrayLikes['Clave']}) "
            . "$condicion order by P.$orden $tipodeorden LIMIT $x, $y";
                
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
                P.Alto, P.Ancho, P.Largo, P.Peso, P._idCategoria, P._idMarca, P.Anios, P.Modelo as _idModelo, P.id_proveedor,
                P.Enviogratis, PR.Proveedor, P.stock, P.Publicar
                from Producto as P 
                inner join Categorias as C on (C._id = P._idCategoria)
                inner join Marcas as M on (M._id = P._idMarca)
                inner join Modelos as V on (V._id = P.Modelo)
                inner join Anios as A on (A._id = P.Anios)
                inner join Proveedor as  PR on (PR._id = P.id_proveedor)
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