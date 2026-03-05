<?php
(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_template($form='principal'){
    $file = __DIR__.'/Html/Clientes_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
        case 'new':
            $html = get_template("principal");
            $html = str_replace("{new}", $vista=="new"? 1:0, $html);
            break;
        case 'perfil':   
            $html = get_template($vista);
            $html = str_replace("{id}", $data["id"], $html);
            break;
        case 'cupones':
            $html = get_template("cupones");
            break;
    }
    print $html;
}

function principal(){
    $data = array();
    $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        switch($opc){
            case 'principal':
            case 'new':
                retorna_vista($opc);
                break;
            case 'perfil':
                $data["id"] = htmlspecialchars($_GET["id"]);
                retorna_vista($opc,$data);
                break;
            case 'cupones':
                retorna_vista($opc);
                break;
            
        }
        
}
//var_dump($_SESSION);
principal();