<?php
    /* 
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
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
                $html = str_replace("{acreditada}", $data["acreditada"], $html);
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
        }
        if(strpos($opc,'cc?') !==false){
            if($formulario["nbResponse"]=="Aprobado"){
                $_SESSION["datacc"]= $formulario;
                unset($_SESSION["CarritoPrueba"]);
                $data["acreditada"] = "¡Compra Acreditada!";
                $data["mensaje"] = "Gracias por su preferencia.";
            }else if ($formulario["nbResponse"]=="Rechazado"){
                $data["mensaje"] = "¡{$formulario["cdResponse"]}!, {$formulario["nb_error"]}";
            }

            retorna_vista("cc?",$data);
        }
    }
    
    principal($array_principal);
    