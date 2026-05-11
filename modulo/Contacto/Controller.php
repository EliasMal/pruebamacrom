<?php

require_once 'View.php';
    function principal(){
         $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
            case 'principal':
                $data["mod"] = "Contacto";
                retorna_vista($opc,$data);
                break;
         }
    }
    
    principal();
