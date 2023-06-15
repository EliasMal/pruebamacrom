<?php
    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

    function get_template($form='principal'){
        $file = __DIR__.'/Html/RepProductos_'.$form.'.html';
        $template = file_get_contents($file);
        return $template;
    }
    
    function retorna_vista($vista,$data=array()){
        switch($vista){
            case 'principal':
                $html = get_template($vista);
                break;
            
        }
        print $html;
    }
    
    function principal(){
        Global $mod;
        $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
         switch($opc){
            case 'principal':
                retorna_vista($opc);
                break;    
        }
        
    }
    
    principal();