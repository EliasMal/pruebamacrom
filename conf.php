<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('MODULO_DEFECTO','home');
define('LAYOUT_DEFECTO','plantilla.php');
define('MODULO_PATH',realpath('./modulo/'));
define('LAYOUT_PATH',realpath('./layout/'));
$conf['home'] = array(
				'archivo' => 'home/Controller.php',
				'layout' => LAYOUT_DEFECTO);
$conf['catalogo'] = array('archivo'=>'Catalogo/Controller.php');
$conf['Compras'] = array('archivo'=>'Compras/Controller.php');
$conf["nosotros"] = array('archivo'=>'Nosotros/Controller.php');
$conf["contacto"] = array('archivo'=>'Contacto/Controller.php');
$conf["Blog"] = array('archivo'=>'Blog/Controller.php');
$conf["login"] = array('archivo'=>'Login/Controller.php');
$conf["register"] = array('archivo'=>'Login/Controllereg.php');
$conf["aviso-de-privacidad"] = array('archivo'=>'Avisoprivacidad/Controller.php');
$conf["Terminos-condiciones"] = array('archivo'=>'TerminosCondiciones/Controller.php');
$conf["Devoluciones"] = array('archivo'=>'Devoluciones/Controller.php');

/* Estas rutas requiren inicio de session */
$conf["ProcesoCompra"] = array('archivo'=>'ProcesoCompra/Controller.php');
$conf["Profile"] = array('archivo'=>'Profile/Controller.php');
