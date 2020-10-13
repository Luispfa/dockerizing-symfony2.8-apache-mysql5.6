<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;

class Header extends PageContainer implements Page{
    var $servicio;
    function __construct($servicio){
        $this->servicio = $servicio;
    }
    
    function getBody($pdf) {
        $client = $this->getContainer()->get('lugh.parameters')->getByKey('Config.customer.title', '');
        
        $date = getdate();
        $dateS= $date['mday']."/".$date['mon']."/".$date['year'];
        
        $pdf->SetFont('calibri', '', 12);
        
        //Imagen de cabecera
        $pdf->SetY(0.5);
        
        //Ojo PDF->Image no FPDF->Image
        $pdf->Image("images/logo.jpg", 1, 0.5, null, null, "JPG", null, "http://www.header.net");
        
        //Tabla de cabecera
        $A = "Estadísticas de ". $this->servicio;
        $AB= $client;
        $B = "Fecha: ";
        $D = "Autor: Dpto. Desarrollo";
        
        $pdf->setDrawColor(146,208,80); //Color de línea
        $pdf->setX(0.5*$pdf->w-1);
        
        //Título de la tabla
        $pdf->titulo3F();
        $pdf->setDrawColor(146,208,80); //Color de línea
        $pdf->Cell(0.5*$pdf->w,0.25,utf8_decode($A),'LRT');
        $pdf->setXY(0.5*$pdf->w-1,0.75);
        $pdf->Cell(0.5*$pdf->w,0.25,utf8_decode($AB),'LR');
        $pdf->mainTextF();
        $pdf->setDrawColor(146,208,80); //Color de línea
        
        //Resto de la tabla
        $pdf->setXY(0.5*$pdf->w-1,1);
        $pdf->Cell(0.5*$pdf->w,0.25,utf8_decode($B).$dateS,'LR');
        $pdf->setX(3*$pdf->w/4-1);
        $pdf->setXY(0.5*$pdf->w-1,1.25);
        $pdf->Cell(0.5*$pdf->w,0.25,utf8_decode($D),'RLB');
    }
}
