<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
include("Seguridad.php");
//session_start();
        
	if(!isset($_SESSION['bandera'])) $_SESSION['bandera']=0;
	date_default_timezone_set('America/Mexico_City');	
	
	
	
	// Primero incluiremos el arcchivo de configuracion
	include('conf.php');
	

	if(!empty($_GET['mod'])){
                $modulo=$_GET['mod'];
	}else{
		$_SESSION['bandera']=0;
		$modulo=MODULO_DEFECTO;
	}	

	if (empty($conf[$modulo]))
		$modulo = MODULO_DEFECTO;
	if(empty($conf[$modulo]['layout']))
		$conf[$modulo]['layout'] = LAYOUT_DEFECTO;
        $mod = isset($_GET["mod"])? $_GET["mod"]:"home";
	
		$path_layout = LAYOUT_PATH.'/'.$conf[$modulo]['layout'];
		$path_modulo = MODULO_PATH.'/'.$conf[$modulo]['archivo'];
			
		if(file_exists($path_layout))
			include($path_layout);
		else
			if(file_exists($path_modulo)){
				include($path_modulo);
                        }else{
				die ('Error el cargar el modulo <b>'.$modulo.'</b> no existe el archivo <b>'.$conf[$modulo]['archivo'].'</b>');
                        }
