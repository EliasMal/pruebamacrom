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
        $formulario = array_map("htmlspecialchars", $_GET);
        $opc = isset($formulario["opc"])? $formulario["opc"]:"principal";
        $data["categoria"] = isset($formulario["cate"])? $formulario["cate"]:"";
        switch($opc){
           case 'principal':
               retorna_vista($opc,$data);
               break;
           case 'detalles':
               $data["id"] = $formulario["_id"];
               retorna_vista($opc,$data);
               break;
           
        }
    }
    
    principal();
    