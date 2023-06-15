<?php
 #este archivo es el controlador de la aplicacion
    require_once 'View.php';
//    require_once 'Model.php';
//    include 'clases/dbconectar.php';
    
    function principal(){
         $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
             case 'principal':
                 retorna_vista($opc);
                 break;
         }
    }
    
    principal();
