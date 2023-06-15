<?php
session_name("loginUsuario");
session_start();
require_once "../../../../Clases/dbconectar.php";
require_once "../../../../Clases/ConexionMySQL.php";
date_default_timezone_set('America/Mexico_City');

class Blog{
    private $conn;
    private $jsonData = array("Bandera"=>false,"mensaje"=>"","Data" => array());
    private $formulario = array();
    private $fecha;
    private $url;

    public function __construct($array) {
        $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
        $this->fecha = date("Y-m-d");
        $this->url = preg_replace("#(admin\.)?#i","", $_SERVER["HTTP_ORIGIN"]);
    }

    public function __destruct() {
        unset($this->conn);
    }
    /**
     * Funcion prinicpal de la clase
     */
    public function main(){
        $this->formulario = array_map("htmlentities", $_POST);
        $this->foto =  isset($_FILES)? $_FILES:array();
        
        
        switch($this->formulario["opc"]){
            case 'new':
                
                if($this->subirImagen()){
                    if($this->setBlog($this->formulario["opc"])){
                        $this->jsonData["Bandera"] = true;
                        $this->jsonData["mensaje"] = "Blog Registrado";
                    }else{
                        $this->jsonData["Bandera"] = false;
                        $this->jsonData["mensaje"] = "Error al intentar guardar los datos";
                    }
                    
                }else{
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error al intentar subir la imagen";
                }
                
                break;
            case 'get':
                $this->jsonData["Bandera"] = true;
                $this->jsonData["Data"]["NoRegistrados"] = $this->getNoEntradas();
                $this->jsonData["Data"]["Registros"] = $this->getEntradas($this->formulario["skip"],$this->formulario["limit"]);
                break;
            case 'getOne':
                $this->jsonData["Bandera"] = true;
                $this->jsonData["Data"] = $this->getOneEntrada($this->formulario["id"]);
                break;
            case 'delete':
                if($this->setBlog($this->formulario["opc"],$this->formulario["id"])){
                    $this->jsonData["Bandera"] = true;
                    $this->jsonData["mensaje"] = "El Blog ha sido desabilitado";
                    $this->jsonData["Data"]["NoRegistrados"] = $this->getNoEntradas();
                    $this->jsonData["Data"]["Registros"] = $this->getEntradas($this->formulario["skip"],$this->formulario["limit"]);
                }else{
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error: al intentar deshabilitar el blog";
                }
                break;
            case 'save':
                if($this->setBlog($this->formulario["opc"],$this->formulario["_id"])){
                    if(isset($_FILES)){
                        $this->subirImagen();
                    }
                    $this->jsonData["Bandera"] = true;
                    $this->jsonData["mensaje"] = "El Blog ha sido Actualizado";
                }else{
                    $this->jsonData["Bandera"] = false;
                    $this->jsonData["mensaje"] = "Error: al guardar el blog";
                }

                break;
        }
        $this->jsonData["dominio"]=$this->url;
        print json_encode($this->jsonData);
    }

    /**
     * Funcion que permite obtener el numero de entradas Activas
     * @return int Retorna el numero de registros encontrados
     */
    private function getNoEntradas (){
        $array = array();
        $sql = "SELECT * FROM Blog where Estatus = 1";
        $this->conn->query($sql);
        return $this->conn->count_rows();   
    }
    /**
     * Funcion para obtener las entradas del blog
     * @param integer $skip valor de la paginacion
     * @param integer $limit valor que permite traer la cantidad de filas de la consulta
     * @return array Devuleve un array con la consulta de la base de datos Blog 
     */
    private function getEntradas ($skip=0, $limit=10){
        $array = array();
        $fechas = array();
        $sql = "SELECT _id, Title,Contenido, DATE(Fecha) as FechaCorta, TIME(Fecha) as HoraCorta,
        Imagen, Publicar, Estatus FROM Blog where Estatus = 1 ORDER BY Fecha desc LIMIT $skip, $limit";
        $id = $this->conn->query($sql);
        while($row = $this->conn->fetch($id)){
            $row["Title"] = html_entity_decode($row["Title"]);
            $row["Estatus"]=$row["Estatus"]==1? true:false;
            $row["ClassEstatus"] = $row["Estatus"]? 'bg-success':'bg-danger';
            $row["Publicar"]=$row["Publicar"]==1? true:false;
            $row["ClassPublicar"]=$row["Publicar"]? 'bg-success':'bg-danger';
            $row["faPublicar"]=$row["Publicar"]? 'fa-check':'fa-close';
            array_push($array, $row);
        }
        return $array;   
    }
    /**
     * funcion que permite obtener solo una entrada
     * @param int $id recibe el $id de la publicacion a buscar
     * @return array Retorna un array con el objeto de la busqueda
     */
    private function getOneEntrada($id){
        $sql = "SELECT * FROM Blog where _id = $id";
        $row = $this->conn->fetch($this->conn->query($sql));
        $row["Title"] = html_entity_decode($row["Title"]);
        $row["Contenido"] = html_entity_decode($row["Contenido"]);
        $row["Estatus"]=$row["Estatus"]==1? true:false;
        $row["Publicar"]=$row["Publicar"]==1? true:false;
        return $row;
    }

    /**
     * @param string $opc Recibe com parametro una cadena ya sea (
     *              new : para crear una nueva entrasa
     *              delete: para eliminar una entrada
     *              save: para guadar cambias a la entrada editada)
     * @param int $id Recibe un id si deseamos editar o eliminar una entrada, por default es null para crear un nueva entrada
     * @return Boolean True si se ejecuto la consulta con exito, False si hubo un error al guardar los datos
     */
    private function setBlog($opc,$id=null){
        $publicar = $this->formulario["Publicar"]=="true"? 1:0;
        $estatus = $this->formulario["Estatus"]=="true"? 1:0;
        switch($opc){
            case 'new':
                $sql = "INSERT INTO Blog (Title, Contenido, Imagen, Publicar, Estatus, imagendestacada) values ('{$this->formulario["Titulo"]}','". $this->formulario["Contenido"]. "',"
                    . "'{$this->formulario["filename1"]}',$publicar,$estatus, '{$this->formulario["filename2"]}')";
                break;
            case 'delete':
                $sql = "UPDATE Blog SET Estatus = $estatus where _id = $id";
                break;
            case 'save':
                $sql = "UPDATE Blog SET Title='".$this->formulario["Title"]."', Contenido='". $this->formulario["Contenido"] 
                ."', Publicar='$publicar', Estatus ='$estatus', Imagen='{$this->formulario["Imagen"]}', imagendestacada='{$this->formulario["imagendestacada"]}'" 
                ."WHERE _id = $id";
                break;
        }
        
        return $this->conn->query($sql)? true: false;
    }
    /**
     * Funcio que permite subir una imagen al servidor
     */
    private function subirImagen(){
        foreach ($this->foto as $key => $value) {
            if($value["name"]!="" and $value["size"]!=0){
                $subdir ="../../../../../../"; 
                $dir = "images/Blog/";
                $archivo = $value["name"];
                if(!is_dir($subdir.$dir)){
                    mkdir($subdir.$dir,0755);
                }
                if($archivo && move_uploaded_file($value["tmp_name"], $subdir.$dir.$archivo)){
                    //$this->rutaimagen= $dir.$archivo;
                    //return true;
                }else{
                    echo "no se subio la imagen";
                }
            }else{
                return false;
            }
        }
        return true;
    }
}

$app = new Blog($array_principal);
$app->main();
    