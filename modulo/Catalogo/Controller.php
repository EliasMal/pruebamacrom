<?php

    require_once 'View.php';
    function principal(){
        $formulario = array_map("htmlspecialchars", $_GET);
        $IDseparado = explode("-",$formulario["_id"]);
        $opc = isset($formulario["opc"])? $formulario["opc"]:"principal";
        $data["categoria"] = isset($formulario["cate"])? $formulario["cate"]:"";
        switch($opc){
           case 'principal':
               retorna_vista($opc,$data);
               break;
           case 'detalles':
               $data["id"] = $IDseparado[0];
               retorna_vista($opc,$data);
               break;
           
        }
    }
    
    principal();
    