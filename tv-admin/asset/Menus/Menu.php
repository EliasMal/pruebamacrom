<?php

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_templateMenu($form='principal'){
  $file = __DIR__.'/Menu_'.$form.'.html';
  return file_exists($file) ? file_get_contents($file) : '';
}

function retorna_vistaMenu($vista, $data=array()){
    if($vista == 'principal'){
        $html = get_templateMenu($vista);
        $html = str_replace('{usr}', $data["usr"], $html);  
        $html = str_replace('{nombrecorto}', $data["nombrecorto"], $html); 
        $html = str_replace('{imagen}', $data["imagen"], $html);  
        $html = str_replace('{menus}', $data["html"], $html);  
        print $html;
    }
}

function menu(){
    global $mod;
    global $array_principal;
    
    require_once __DIR__ . "/../Clases/dbconectar.php"; 
    require_once __DIR__ . "/../Clases/ConexionMySQL.php";
    
    $conn = new HelperMySql($array_principal["server"], $array_principal["user"], $array_principal["pass"], $array_principal["db"]);
    
    $icons = array(
        "grupos" => array(
            "Control"=>"fa-fan", "Configuracion"=>"fa-cogs", "Secciones"=>"fa-puzzle-piece", 
            "Respaldo"=>"fa-refresh", "Importar"=>"fa-upload", "Mantenimiento"=>"fa-cog", "Reportes"=>"fa-book"
        ),
        "titulo" => array(
            "Refacciones"=>"fa-toolbox", "Pedidos"=>"fa-shopping-cart", "Clientes"=>"fa-address-book",
            "Categorias"=>"ion-settings", "Agencias"=>"fa-warehouse", "Vehiculos" => "fa-car", 
            "Usuarios"=>"fa-users", "Principal"=>"fa-columns", "Actualizar precios"=>"fa-dollar-sign",
            "Contacto"=>"fa-id-badge", "Codigos Postales"=>"fa-map-marker-alt", "Costos Envios"=>"fa-comment-dollar", 
            "Proveedores"=>"fa-truck", "Blog"=>"fa-cubes", "Correo"=>"fa-envelope", 
            "Pruebas"=>"fa-file", "Refacciones capturadas"=>"fa-list",
            "Permisos"=>"fa-lock", "Bitacora"=>"fa fa-user-secret"
        )
    );

    $rolActual = isset($_SESSION["rol"]) ? $_SESSION["rol"] : '';
    $rol_seguro = addslashes($rolActual);
    
    $sql = "SELECT DISTINCT m.grupo, m.titulo, m.opc 
            FROM Modulos_Admin m 
            INNER JOIN Permisos_Roles p ON m.id_modulo = p.id_modulo 
            WHERE p.rol_nombre = '$rol_seguro'
            ORDER BY FIELD(m.grupo, 'Control', 'Configuracion', 'Secciones', 'Respaldo', 'Importar', 'Mantenimiento', 'Reportes'), m.titulo ASC";
            
    $opcMenu = $conn->fetch_all($conn->query($sql));

    $tempMod = "?mod=$mod";
    $grupoActivo = "";

    if($mod != "home" && $mod != "Perfil"){
        foreach ($opcMenu as $item) {
            if ($tempMod == $item["opc"]) {
                $grupoActivo = $item["grupo"];
                break;
            }
        }
    }

    //Generación del HTML del Menú
    $htmlMenu = "";
    $seccionActual = "";

    foreach ($opcMenu as $item) {
        $grupo = $item["grupo"];
        $titulo = $item["titulo"];
        $url = $item["opc"];
        
        // Cambio de grupo
        if ($seccionActual != $grupo) {
            if ($seccionActual != "") {
                $htmlMenu .= "</ul></li>";
            }
            $seccionActual = $grupo;
            
            $isActiveGroup = ($grupoActivo == $grupo) ? "active" : "";
            $isMenuOpen = ($grupoActivo == $grupo) ? "menu-open" : "";
            $iconoGrupo = isset($icons["grupos"][$grupo]) ? $icons["grupos"][$grupo] : "fa-folder"; 
            
            $htmlMenu .= "
            <li class='nav-item has-treeview $isMenuOpen'>
                <a href='#' class='nav-link $isActiveGroup'>
                    <i class='nav-icon fa $iconoGrupo'></i>
                    <p>
                        $grupo
                        <i class='right fa fa-angle-left'></i>
                    </p>
                </a>
                <ul class='nav nav-treeview'>";
        }
        
        $isActiveItem = ($tempMod == $url) ? "active" : "";
        $iconoTitulo = isset($icons["titulo"][$titulo]) ? $icons["titulo"][$titulo] : "fa-circle-o"; 

        $htmlMenu .= "
            <li class='nav-item'>
                <a href='$url' class='nav-link $isActiveItem' style='padding-left: 40px;'>
                    <i class='fa $iconoTitulo nav-icon'></i>
                    <p>$titulo</p>
                </a>
            </li>";
    }
    
    if ($seccionActual != "") {
        $htmlMenu .= "</ul></li>";
    }

    //Preparar los datos finales y mandarlos a la vista
    $usr = isset($_SESSION["usr"]) ? $_SESSION["usr"] : '';
    $imagenPath = "Images/usuarios/$usr.png";
    
    $data = array(
        "html" => $htmlMenu,
        "usr" => $usr,
        "nombrecorto" => isset($_SESSION["nombrecorto"]) ? $_SESSION["nombrecorto"] : '',
        "imagen" => file_exists($imagenPath) ? $imagenPath : "Images/Avatar Lobo Macrom Grande.png"
    );

    retorna_vistaMenu("principal", $data);
}

menu();