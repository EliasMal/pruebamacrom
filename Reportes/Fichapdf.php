<?php
    $pdf = new FPDF('P','mm','A4');
    $pdf->SetMargins(8,20,8,8);
    $pdf->AddPage();
    $pdf->image($logo,10,20,0,0);
    
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(80,5,"",0,0,'C');
    $pdf->Cell(100,5,"FICHA DE DEPOSITO",0,1,'C');
    $pdf->Cell(195,5,'',0,1,'C');
    $pdf->Cell(195,5,'','B',1,'C');
    $pdf->Cell(195,5,'',0,1,'C');
    
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(195,5,'Datos de la cuenta',0,1,'C');
    $pdf->Cell(195,5,'',0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(64,5,"Banco",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"Nombre de la Cuenta",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"Numero de cuenta",0,1,'');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(64,5,"Santander",1,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,utf8_decode("Néstor Omar Lara Galindo"),1,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"014090655061115796",1,1,'');

    $pdf->Cell(195,5,'',0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(64,5,"No. Orden o Referencia",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"Clabe para transferencia",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"",0,1,'');

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(64,5,$arraytemp["noPedido"],1,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"014090655061115796",1,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"",0,1,'');

    $pdf->Cell(195,5,'',0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(129.5,5,"",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"IMPORTE A PAGAR MXN",0,1,'');
    $pdf->SetFont('Arial','B',22);
    $pdf->Cell(129.5,5,"",0,0,'');
    $pdf->Cell(1.5,5,"",0,0,'');
    $pdf->Cell(64,5,"$ ".number_format($arraytemp["Importe"],2,'.',','),0,1,'');
    
    $pdf->Cell(195,5,'',0,1,'C');
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(195,5,utf8_decode("Nota: Esta ficha de deposito es solo de carácter informativo para cualquier duda o aclaración, No tiene validez oficial como comprobande legal o fiscal"),0,0,'C');
    $pdf->Output();