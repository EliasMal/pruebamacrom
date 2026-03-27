<?php
    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));
    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/bootstrap.php';
    
    function get_template($form){
        $file = __DIR__.'/Html/ProcesoCompra_'.$form.'.html';
        if(file_exists($file)){
            return file_get_contents($file);
        }
        return "";
    }
    
    function retorna_vista($vista, $data=array()){
        $archivo_html = "principal";
        $html_extra = "";

        if(strpos($vista, 'cc?') !== false){
            $archivo_html = "cc";
        } else if (in_array($vista, ['principal', 'paso2', 'paso3'])) {
            $archivo_html = $vista;
        }

        $html = get_template($archivo_html);

        if($archivo_html == 'cc'){
            $html = str_replace("{mensaje}", isset($data["mensaje"]) ? $data["mensaje"] : "", $html);
            $html = str_replace("{acreditada}", isset($data["acreditada"]) ? $data["acreditada"] : "", $html);
        }

        $html = str_replace("{mod}", $data["mod"], $html);
        print $html;
    }
    
    // Función principal router
    function principal($array_principal){
        $formulario_get = array_map("htmlspecialchars", $_GET);
        $opc = isset($_GET['opc']) ? htmlspecialchars($_GET['opc']) : "principal";
        $data["mod"] = "ProcesoCompra";
        
        if(strpos($opc, 'cc?') !==false){
            if(isset($formulario_get["nbResponse"]) && $formulario_get["nbResponse"] == "Aprobado"){
                unset($_SESSION["CarritoPrueba"]);
                $data["acreditada"] = "¡Compra Acreditada!";
                $data["mensaje"] = "Gracias por su preferencia.";
            } else if (isset($formulario_get["nbResponse"]) && $formulario_get["nbResponse"] == "Rechazado"){
                $nb_error = isset($formulario_get["nb_error"]) ? $formulario_get["nb_error"] : "Error desconocido";
                $cd_resp = isset($formulario_get["cdResponse"]) ? $formulario_get["cdResponse"] : "";
                $data["mensaje"] = "¡{$cd_resp}!, {$nb_error}";
            } else {
                 $data["mensaje"] = "Error desconocido en la transacción.";
            }
        }
        
        retorna_vista($opc, $data);
    }
    
    principal($array_principal);
?>