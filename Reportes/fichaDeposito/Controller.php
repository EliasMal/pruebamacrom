<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));
include("Model.php");

function get_template($form='principal'){
    $file = __DIR__.'/Html/fichaDeposito_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_template($vista);
            //$html = str_replace("{importe}", $data["importe"], $html);
            //$html = str_replace("{noPedido}", $data["noPedido"], $html);
            break;
    }
    print $html;
}

function principal(){
        $formulario = array_map("htmlspecialchars",$_GET);
         $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
            case 'principal':
                //$arraytemp = $conn->getImportecompra($formulario["_id"]);
                retorna_vista($opc,$data);
                break;
         }
    }
    
    principal();