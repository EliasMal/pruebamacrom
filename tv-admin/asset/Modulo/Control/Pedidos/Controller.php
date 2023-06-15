<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_template($form='principal'){
    $file = __DIR__.'/Html/Pedidos_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
        case 'new':
            $html = get_template($vista);
            $html = str_replace("{autorizacion}", $data["autorizacion"], $html);
            break;
        case 'detalles':   
            $html = get_template($vista);
            $html = str_replace("{id}", $data["id"], $html);
            $html = str_replace("{autorizacion}", $data["autorizacion"], $html);
            break;
        default:
            
        break;
    }
    print $html;
}

function principal($func){
    $data = array();
    /**
     * esta linea de codigo es solo para autorizar quien tiene acceso al modulo
     */
    $data["autorizacion"] = $func::siAcceso("root,Admin,ventas");
    $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        switch($opc){
            case 'principal':
            case 'new':
                retorna_vista($opc,$data);
                break;
            case 'detalles':
                $data["id"] = htmlspecialchars($_GET["id"]);
                retorna_vista($opc,$data);
                break;
            
        }
    
        
}

$func = new Funciones();
principal($func);