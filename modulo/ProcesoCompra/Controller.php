<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include("Model.php");

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));



function get_template($form='principal'){
    $file = __DIR__.'/Html/ProcesoCompra_'.$form.'.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($vista,$data=array()){
    switch($vista){
        case 'principal':
            $html = get_template($vista);
            
            break;
        case 'paso2':
            $html = get_template($vista);
            break;
        case 'paso3':
            $html = get_template($vista);
            break;
        case 'cc?':
            
            $html = get_template("cc");
            $html = str_replace("{mensaje}", $data["mensaje"], $html);
        break;
    }
    $html = str_replace("{mod}", $data["mod"], $html);
    print $html;
}

function principal($array_principal){
        $formulario = array_map("htmlspecialchars",$_GET);
        $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        $data["mod"] = "ProcesoCompra";
         switch($opc){
            
            case 'principal':
               
                retorna_vista($opc,$data);
                break;
            case 'paso2':
                
                retorna_vista($opc,$data);
                break;
            case 'paso3':
                retorna_vista($opc,$data);
                break;
            case 'cc?':
                if($formulario["nbResponse"]=="Aprobado"){
                        unset($_SESSION["cart"]);
                        unset($_SESSION["id_pedido"]);
                    $data["mensaje"]="¡{Compra Aprobada}! gracias por su preferencia, a la brevedad le llegara 
                    un correo con su numero de guia de su pedido";
                }else if ($formulario["nbResponse"]=="Rechazado"){
                    $data["mensaje"] = "¡{$formulario["cdResponse"]}!, {$formulario["nb_error"]}";
                }
                
                retorna_vista($opc,$data);
            break;
         }
    }
    
    principal($array_principal);
