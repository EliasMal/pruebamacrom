<?php

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_templateMenu($form='principal'){
  $file = __DIR__.'/Menu_'.$form.'.html';
  $template = file_get_contents($file);
  return $template;
}

function retorna_vistaMenu($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_templateMenu($vista);
            $html = str_replace('{usr}', $data["usr"], $html);  
            $html = str_replace('{nombrecorto}', $data["nombrecorto"], $html); 
            $html = str_replace('{imagen}', $data["imagen"], $html);  
            $html = str_replace('{menus}', $data["html"], $html);  
            break;

    }
    print $html;
}

function menu(){
    Global $mod;
    $opc = "principal";
    $opcMenu = array();
    $sum = 0;
    $seccion = "";
    $icons = array("grupos"=>array("Control"=>"fa-fan", "Configuracion"=>"fa-cogs","Secciones"=>"fa-puzzle-piece", 
    "Respaldo"=>"fa-refresh", "Importar"=>"fa-upload", "Mantenimiento"=>"fa-cog", "Reportes"=>"fa-book"),
                   "titulo"=>array("Refacciones"=>"fa-toolbox","Pedidos"=>"fa-shopping-cart", "Clientes"=>"fa-address-book","Categorias"=>"ion-settings",
                   "Agencias"=>"fa-warehouse", "Vehiculos" => "fa-car", "Usuarios"=>"fa-users", "Principal"=>"fa-columns", "Actualizar precios"=>"fa-dollar",
                   "Contacto"=>"fa-id-badge", "Codigos Postales"=>"", "Costos Envios"=>"fa-comment-dollar", "Proveedores"=>"fa-truck", "Blog"=>"fa-cubes", 
                   "Correo"=>"fa-envelope", "Pruebas"=>"fa-file", "Refacciones capturadas"=>"fa-list")
    );
    $data["html"] = "";
    $data["usr"] = $_SESSION["usr"];
    $data["nombrecorto"] = $_SESSION["nombrecorto"];
    $data["imagen"] = file_exists("Images/usuarios/{$_SESSION["usr"]}.png")? "Images/usuarios/{$_SESSION["usr"]}.png":"Images/Avatar Lobo Macrom Grande.png";
    
    switch($_SESSION["rol"]){
        case 'root':
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=Refacciones";       $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Pedidos";                $opcMenu[$sum]["opc"]="?mod=Pedidos";           $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Clientes";               $opcMenu[$sum]["opc"]="?mod=Clientes";          $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Contacto";               $opcMenu[$sum]["opc"]="?mod=Contacto";          $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Principal";              $opcMenu[$sum]["opc"]="?mod=webprincipal";      $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Blog";                   $opcMenu[$sum]["opc"]="?mod=Blog";              $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Categorias";             $opcMenu[$sum]["opc"]="?mod=Categorias";        $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Agencias";               $opcMenu[$sum]["opc"]="?mod=Marcas";            $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Vehiculos";              $opcMenu[$sum]["opc"]="?mod=Modelos";           $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Usuarios";               $opcMenu[$sum]["opc"]="?mod=usuarios";          $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Costos Envios";          $opcMenu[$sum]["opc"]="?mod=Cenvios";           $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Proveedores";            $opcMenu[$sum]["opc"]="?mod=Proveedores";       $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Correo";                 $opcMenu[$sum]["opc"]="?mod=Correo";            $sum++;
            $opcMenu[$sum]["grupo"]="Importar";             $opcMenu[$sum]["titulo"]= "Codigos Postales";       $opcMenu[$sum]["opc"]="?mod=CPostales";         $sum++;
            $opcMenu[$sum]["grupo"]="Importar";             $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=IRefacciones";      $sum++;
            $opcMenu[$sum]["grupo"]="Respaldo";             $opcMenu[$sum]["titulo"]= "Actualizar precios";     $opcMenu[$sum]["opc"]="?mod=Actualizarpre";     $sum++;
            $opcMenu[$sum]["grupo"]="Respaldo";             $opcMenu[$sum]["titulo"]= "Pruebas";                $opcMenu[$sum]["opc"]="?mod=Pruebas";           $sum++;
            $opcMenu[$sum]["grupo"]="Reportes";             $opcMenu[$sum]["titulo"]= "Refacciones capturadas"; $opcMenu[$sum]["opc"]="?mod=RepProductos";      $sum++;
            $opcMenu[$sum]["grupo"]="Mantenimiento";        $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=repRefacciones";    $sum++;
        break;
        case 'Web':
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Principal";              $opcMenu[$sum]["opc"]="?mod=webprincipal";      $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Blog";                   $opcMenu[$sum]["opc"]="?mod=Blog";              $sum++;
        break;
        case 'Admin':
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=Refacciones";       $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Pedidos";                $opcMenu[$sum]["opc"]="?mod=Pedidos";           $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Clientes";               $opcMenu[$sum]["opc"]="?mod=Clientes";          $sum++;
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Contacto";               $opcMenu[$sum]["opc"]="?mod=Contacto";          $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Blog";                   $opcMenu[$sum]["opc"]="?mod=Blog";              $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Categorias";             $opcMenu[$sum]["opc"]="?mod=Categorias";        $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Agencias";               $opcMenu[$sum]["opc"]="?mod=Marcas";            $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Vehiculos";              $opcMenu[$sum]["opc"]="?mod=Modelos";           $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Costos Envios";          $opcMenu[$sum]["opc"]="?mod=Cenvios";           $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Proveedores";            $opcMenu[$sum]["opc"]="?mod=Proveedores";       $sum++;
            $opcMenu[$sum]["grupo"]="Respaldo";             $opcMenu[$sum]["titulo"]= "Actualizar precios";     $opcMenu[$sum]["opc"]="?mod=Actualizarpre";     $sum++;
            // $opcMenu[$sum]["grupo"]="Reportes";             $opcMenu[$sum]["titulo"]= "Refacciones capturadas"; $opcMenu[$sum]["opc"]="?mod=RepProductos";      $sum++;
        break;
        case 'user':
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=Refacciones";       $sum++;
        break;
        case 'capturista':
            $opcMenu[$sum]["grupo"]="Control";              $opcMenu[$sum]["titulo"]= "Refacciones";            $opcMenu[$sum]["opc"]="?mod=Refacciones";       $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Categorias";             $opcMenu[$sum]["opc"]="?mod=Categorias";        $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Agencias";               $opcMenu[$sum]["opc"]="?mod=Marcas";            $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Vehiculos";              $opcMenu[$sum]["opc"]="?mod=Modelos";           $sum++;
            $opcMenu[$sum]["grupo"]="Configuracion";        $opcMenu[$sum]["titulo"]= "Proveedores";            $opcMenu[$sum]["opc"]="?mod=Proveedores";       $sum++;
            $opcMenu[$sum]["grupo"]="Respaldo";             $opcMenu[$sum]["titulo"]= "Actualizar precios";     $opcMenu[$sum]["opc"]="?mod=Actualizarpre";     $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Blog";                   $opcMenu[$sum]["opc"]="?mod=Blog";              $sum++;
            $opcMenu[$sum]["grupo"]="Secciones";            $opcMenu[$sum]["titulo"]= "Principal";              $opcMenu[$sum]["opc"]="?mod=webprincipal";      $sum++;
        break;
            break;
    }
    $conM = count($opcMenu);
    $tempMod = "?mod=$mod";
    $grupo = "";
    
    /*Recorremos el arreglo para seber cual grupo debemos de activar*/
    if($mod != "home" && $mod != "Perfil"){
        for($i=0;$i<$conM;$i++){
            if(strcmp($tempMod,$opcMenu[$i]["opc"])==0){
                $grupo = $opcMenu[$i]["grupo"];
                break;
            }
        }
    }

    for($x=0;$x<$conM;$x++){
        if(isset($opcMenu[$x]["grupo"])){
            if ($seccion != $opcMenu[$x]["grupo"]){
                if($seccion != ""){
                    $data["html"].= "</ul></li>";
                }
                $seccion =  $opcMenu[$x]["grupo"];
                $active =  strcmp($grupo, $opcMenu[$x]["grupo"])==0? "active":"";
                $menuopen  = strcmp($grupo, $opcMenu[$x]["grupo"])==0? "menu-open":"";
                $data["html"] .= "
                <li class='nav-item has-treeview $menuopen'>
                    <a href='#' class='nav-link $active'>
                        <i class='nav-icon fa {$icons["grupos"][$seccion]}'></i>
                        <p>
                            $seccion
                            <i class='right fa fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>";
            }
            $active = strcmp($tempMod,$opcMenu[$x]["opc"])==0? "active":"";
            $titulo = $opcMenu[$x]["titulo"];
            $data["html"] .= "
                <li class='nav-item'>
                    <a href='{$opcMenu[$x]["opc"]}' class='nav-link $active'>
                        <i class='fa {$icons["titulo"][$titulo]} nav-icon'></i>
                        <p>$titulo</p>
                    </a>
                </li>";
        }
    }
    $data["html"].= "</ul></li>";

    retorna_vistaMenu($opc,$data);
}

menu();