<?php
    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));
    
    function get_template($form){
        $file = __DIR__.'/Html/ProcesoCompra_'.$form.'.html';
        if(file_exists($file)) {
            return file_get_contents($file);
        }
        return "<h3>Error: Vista no encontrada ($form)</h3>";
    }
    
    function retorna_vista($vista, $data=array()){
        $archivo_vista = strpos($vista, 'cc?') !== false ? 'cc' : $vista;
        
        $html = get_template($archivo_vista);
        
        if($archivo_vista == 'cc') {
            $html = str_replace("{mensaje}", isset($data["mensaje"]) ? $data["mensaje"] : "", $html);
            $html = str_replace("{acreditada}", isset($data["acreditada"]) ? $data["acreditada"] : "", $html);
        }
        
        $html = str_replace("{mod}", isset($data["mod"]) ? $data["mod"] : "ProcesoCompra", $html);
        print $html;
    }
    
    function principal($array_principal){
        $formulario = array_map("htmlspecialchars", $_GET);
        $opc = isset($_GET['opc']) ? htmlspecialchars($_GET['opc']) : "principal";
        $data["mod"] = "ProcesoCompra";
        
        if(strpos($opc, 'cc?') !== false){
            if(isset($formulario["nbResponse"]) && $formulario["nbResponse"] == "Aprobado"){
                unset($_SESSION["CarritoPrueba"]);
                $data["acreditada"] = "¡Compra Acreditada!";
                $data["mensaje"] = "Gracias por su preferencia.";
            } else if (isset($formulario["nbResponse"]) && $formulario["nbResponse"] == "Rechazado"){
                $nb_error = isset($formulario["nb_error"]) ? $formulario["nb_error"] : "Error desconocido";
                $cd_resp = isset($formulario["cdResponse"]) ? $formulario["cdResponse"] : "Denegado";
                $data["mensaje"] = "¡{$cd_resp}!, {$nb_error}";
            }
        }
        
        retorna_vista($opc, $data);
    }
    
    principal($array_principal);
?>