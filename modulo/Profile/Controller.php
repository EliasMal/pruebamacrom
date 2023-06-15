<?php

include("Model.php");

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));



function get_template($form='principal'){
    $file = __DIR__.'/Html/Profile_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_template($vista);
            break;
        case 'Mispedidos':
        case 'Mispedidos_view':
            $html = get_template($vista);
        break;
        case 'Session':
            $html = get_template($vista);
        break;
        case 'Direcciones':
        case 'Direcciones_add':
        case 'Direcciones_edit':
            $html = get_template($vista);
            if(isset($data["id"])){
                $html = str_replace("{id}", $data["id"], $html);
            }
        break;
        case 'Monedero':
            $html = get_template($vista);
        break;
        case 'Facturacion':
        case 'Facturacion_add':
        case 'Facturacion_edit':
            $html = get_template($vista);
        break;
    }
    $html = str_replace("{opc}", $data["opc"], $html);
    print $html;
}

function principal($array_principal){
    
    /* if($_SESSION["autentificacion"]!= 1){
        
    }else{ */
        
        $formulario = array_map("htmlspecialchars",$_GET);
        $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        
        $data["opc"] = $opc;
        switch($opc){
            case 'principal':
                retorna_vista($opc, $data);
            break;
            case 'Mispedidos':
            case 'Mispedidos_view':
                retorna_vista($opc, $data);
            break;
            case 'Session':
                retorna_vista($opc, $data);
            break;
            case 'Direcciones':
            case 'Direcciones_add':
            case 'Direcciones_edit';
                $data["id"] = isset($_GET['id'])? htmlspecialchars($_GET['id']):"";
                retorna_vista($opc, $data);
            break;
            
            case 'Monedero':
                retorna_vista($opc, $data);
            break;
            case 'Facturacion':
            case 'Facturacion_add':
            case 'Facturacion_edit':
                retorna_vista($opc, $data);
            break;
        }
    //}
}

principal($array_principal);