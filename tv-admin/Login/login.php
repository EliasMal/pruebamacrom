<?php
    (@__DIR__ == '__DIR__') && define('__DIR__',  realpath(dirname(__FILE__)));
    $html = "";
    $error = isset($_GET["error"])? $_GET["error"]:"";
    $file = __DIR__.'/html/login.html';
    $template = file_get_contents($file);
    if($error!=""){
        $html = "<div class='alert alert-danger' role='alert' ><b>Acceso denegado</b></div>";
    }
    $template = str_replace('{error}', $html, $template);
    print $template;