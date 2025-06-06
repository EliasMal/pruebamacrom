<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

function get_template($form='principal'){
    $file = __DIR__.'/Html/Catalogo_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_template($vista);
            $html = str_replace("{categoria}", $data["categoria"], $html);
        break;
        case 'detalles':
            $html = get_template($vista);
            $html = str_replace("{id}", $data["id"], $html);
        break;
    }
    print $html;
}
