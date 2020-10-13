<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;

class TitlePageWeekly extends PageContainer implements Page{
    var $servicio;
    function __construct($servicio){
        $this->servicio = $servicio;
    }
    
    public function getBody($pdf){
        $client = $this->getContainer()->get('lugh.parameters')->getByKey('Config.customer.title', '');

        $pdf->SetFont('calibri', '', 30);

        //Título primera página
        $pdf->setXY(0.5,$pdf->h*0.4);
        $pdf->MultiCell($pdf->w-1,1,utf8_decode("Informe semanal de\n".$this->servicio."\n".$client),0,'C');
    }
}
