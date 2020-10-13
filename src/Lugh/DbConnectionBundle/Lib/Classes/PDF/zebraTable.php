<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

/**
 * PDF Class
 *
 * @package PiwikReportGen
 * @author Header S.L.
 */

class zebraTable {
    var $pairRow;
    
    function titleCell($pdf, $text, $width){
        $this->pairRow = false;
        
        $pdf->SetFont('calibri','B',12);
        $pdf->SetTextColor(118,146,60);
        $pdf->SetDrawColor(118,146,60);
        
        $pdf->Cell($width,.25,utf8_decode($text),'TB',0,'L');
    }
    
    function newRow($pdf){
        $pdf->SetY($pdf->GetY()+.25);                     //Go to next line and
        $this->pairRow = ($this->pairRow) ? false : true; //Switch pairRow
    }
    
    function rowCell($pdf, $text, $width){       
        $pdf->SetFont('calibri','B',12);
        $pdf->SetTextColor(118,146,60);
        $pdf->SetDrawColor(118,146,60);
        
        ($this->pairRow) ? $pdf->setFillColor(230,238,213) : $pdf->setFillColor(255,255,255);
        
        $pdf->Cell($width,.25,utf8_decode($text),0,0,'L',true);
    }
}
