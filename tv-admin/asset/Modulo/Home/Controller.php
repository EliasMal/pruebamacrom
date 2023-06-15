<?php

    require_once "View.php";
    
    function principal(){
        Global $mod;
        $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
            case 'principal':
                retorna_vista($opc);
                break;    
        }
        
    }
    
    principal();