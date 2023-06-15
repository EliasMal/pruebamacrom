<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    session_name("loginCliente");
    session_start();
/*require './Html/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf('P','A4','es','true','UTF-8');
$html2pdf->output('ComprobanteDePago.pdf');*/

    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));
    
    function get_template($form='principal'){
        $file = __DIR__.'/Html/ComprobantePago_'.$form.'.html';
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
        $formulario = array_map("htmlspecialchars",$_GET);
        $opc = isset($_GET['opc'])? htmlspecialchars($_GET['opc']):"principal";
        switch($opc){
            case 'principal':
            //$html2pdf->writeHTML($html);
                retorna_vista($opc);
            break;
        }
    }

    principal();