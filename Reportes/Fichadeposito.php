<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_name("loginCliente");
session_start();

(@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));

require_once "../tv-admin/asset/Includes/Fpdf/fpdf.php";
include("Model.php");



/* function get_template(){
    $file = __DIR__.'/Fichadeposito.html';
    $template = file_get_contents($file);
    return $template;
}

function retorna_vista($data=array()){
    $html = get_template();
    $html = str_replace("{importe}", $data["Importe"], $html);
    print $html;
}

function principal2(){
    global $array_principal;
    $conn = new Model($array_principal);    
    $id = isset($_GET['_id'])? htmlspecialchars($_GET['_id']):"";
    $arraytemp = $conn->getImportecompra($id);
    $data["Importe"]  = number_format($arraytemp["Importe"],2,'.',',');
    retorna_vista($data);
} */

function principal(){
    global $array_principal;
    $conn = new Model($array_principal);    
    $id = isset($_GET['_id'])? htmlspecialchars($_GET['_id']):"";
    $arraytemp = $conn->getImportecompra($id);
    //var_dump($arraytemp);
    $logo = "https://macromautopartes.com/images/icons/logomacrom.png";
    require_once "Fichapdf.php";
}
    
principal();