<?php
    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

    function get_template($form='principal'){
        $file = __DIR__.'/Html/Login_'.$form.'.html';
        if(file_exists($file)){
            return file_get_contents($file);
        }
        return "<h3 style='text-align:center; padding: 4rem; color: #de0007;'>Error: Vista de Login no encontrada ($form)</h3>";
    }

    function retorna_vista($vista, $data=array()){
        switch($vista){
            case 'principal':
                $html = get_template($vista);
                $html = str_replace("{mod}", $data["mod"], $html);
                break;
        }
        print $html;
    }

    function principal(){
         $opc = isset($_GET['opc']) ? htmlspecialchars($_GET['opc']) : "principal";
         switch($opc){
            case 'principal':
                $data["mod"] = "Session";
                retorna_vista($opc, $data);
                break;
         }
    }
    
    principal();
?>