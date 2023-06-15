<?php

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_template($form='principal'){
    $file = __DIR__.'/Html/Contacto_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_template($vista);
        break;
        case 'detalles':
            $html = get_template($vista);
            $html = str_replace("{_id}", $data["_id"], $html);
        break;
    }
    print $html;
}

function principal(){
    $data = array();
    $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        switch($opc){
            case 'principal':
                retorna_vista($opc);
                break;
            case 'detalles':
                $data["_id"] = isset($_GET['id'])? htmlspecialchars($_GET['id']):"";
                retorna_vista($opc, $data);
                break;
            
        }
}

principal();