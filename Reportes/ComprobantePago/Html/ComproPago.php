<?php

require __DIR__.'/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;
//Recoger Contenido de fichero 2
ob_start();
require_once "ComprobantePago_principal.html";
$html = ob_get_clean();
//$html2pdf->loadHtmlFile("ComprobantePago_principal.html");
$html2pdf = new Html2Pdf('P','A4','es','true','UTF-8');
$html2pdf->writeHTML($html);
$html2pdf->output('ComprobanteDePago.pdf');

