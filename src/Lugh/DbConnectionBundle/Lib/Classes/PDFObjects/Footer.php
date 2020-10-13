<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;

class Footer implements Page{
    public function getBody($pdf){
        if($pdf->PageNo()==1) return;
        
        $pdf->mainTextF();
        $pdf->SetY($pdf->h-1);
        $pdf->Cell(0, .25, utf8_decode("Página ").($pdf->PageNo()-1), 'T', 2, "R");
    }
}
