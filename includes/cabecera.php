<?php
    
    //var_dump($_SESSION);
    $file ='./html/cabecera.html';
    $template = file_get_contents($file);
    print $template;


