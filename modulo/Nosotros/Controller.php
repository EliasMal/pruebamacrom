<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
   require_once 'View.php';
//    require_once 'Model.php';
//    include 'clases/dbconectar.php';
    
    function principal(){
         $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
            case 'principal':
                $data["mod"] = "Nosotros";
                retorna_vista($opc,$data);
                break;
         }
    }
    
    principal();