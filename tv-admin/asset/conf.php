
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('MODULO_DEFECTO','home');
define('LAYOUT_DEFECTO','plantilla.php');
define('MODULO_PATH',realpath('./Modulo/'));
define('LAYOUT_PATH',realpath('./Layout/'));
$conf['home'] = array(
				'archivo' => 'Home/Controller.php',
				'layout' => LAYOUT_DEFECTO,
				'permisos'=>["root","Admin","user","venta","Web","capturista"]);
/*Menu Configuracion*/
$conf['usuarios'] = array('archivo'=>'Configuracion/Usuarios/Controller.php',"permisos"=>["root"]);
$conf['Categorias'] = array('archivo'=>'Configuracion/Categorias/Controller.php',"permisos"=>["root","Admin","capturista"]);
$conf['Marcas'] = array('archivo'=>"Configuracion/Marcas/Controller.php","permisos"=>["root","Admin","capturista"]);
$conf['Modelos'] = array('archivo'=>"Configuracion/Modelos/Controller.php","permisos"=>["root","Admin","capturista"]);

//$conf['Anios'] = array('archivo'=>"Configuracion/Anios/Controller.php","permisos"=>["root","Admin","capturista"]);
$conf['Cenvios'] = array('archivo'=>"Configuracion/Cenvios/Controller.php","permisos"=>["root","Admin"]);
$conf["Proveedores"] = array('archivo'=>'Configuracion/Proveedores/Controller.php',"permisos"=>["root","Admin","capturista"]);
$conf["Perfil"] = array('archivo'=>'Configuracion/Perfil/Controller.php',"permisos"=>["root","Admin","user","venta","Web","capturista"]);
$conf["Correo"] = array('archivo'=>'Configuracion/Correo/Controller.php',"permisos"=>["root","Admin"]);

/*Menu Control*/
$conf['Refacciones'] = array('archivo'=>"Control/Refacciones/Controller.php",'permisos'=>["root","Admin","user","venta","web","capturista"]);
$conf['Pedidos'] = array('archivo'=>"Control/Pedidos/Controller.php",'permisos'=>["root","Admin","venta"]);
$conf['Clientes'] = array('archivo'=>"Control/Clientes/Controller.php",'permisos'=>["root","Admin"]);
$conf['Contacto'] = array('archivo'=>"Control/Contacto/Controller.php","permisos"=>["root","Admin"]);

/*Menu Secciones */
$conf['webprincipal'] = array("archivo"=>"Secciones/webprincipal/Controller.php","permisos"=>["root","Admin","Web"]);
$conf['Blog'] = array("archivo"=>"Secciones/Blog/Controller.php","permisos"=>["root","Admin","Web","capturista"]);

/*Menu Respaldo */
$conf['Actualizarpre'] = array("archivo"=>"Respaldo/Precios/Controller.php","permisos"=>["root","Admin","capturista"]);
$conf['Pruebas'] = array("archivo"=>"Respaldo/Pruebas/Controller.php","permisos"=>["root"]);

/*Menu Importar */
$conf['CPostales'] = array("archivo"=>"Importar/CPostales/Controller.php","permisos"=>["root","Admin"]);
$conf['IRefacciones'] = array("archivo"=>"Importar/IRefacciones/Controller.php","permisos"=>["root","Admin"]);

/**Menu Mantenimiento */
$conf["repRefacciones"] = array("archivo"=>"Mantenimiento/repRefacciones/Controller.php","permisos"=>["root"]);

$conf["RepProductos"] = array("archivo"=>"Reportes/RepProductos/Controller.php","permisos"=>["root","Admin"]);

