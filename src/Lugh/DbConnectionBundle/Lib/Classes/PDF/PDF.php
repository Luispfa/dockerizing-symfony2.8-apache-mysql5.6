<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

use Lugh\DbConnectionBundle\Lib\Vendor\fpdf\FPDF;

/**
 * PDF Class
 * @author Header S.L.
 */

class PDF extends FPDF {

    var $header;
    var $footer;
    
    function __construct($header, $footer, $orientation='P', $unit='in', $size='A4'){
        parent::FPDF($orientation,$unit,$size);
        
        $this->header = $header;
        $this->footer = $footer;
        
        $this->addFont('calibri','', 'calibri.php');    //Calibri
        $this->addFont('calibri','B','calibrib.php');   //Calibri negrita
        
        $this->SetMargins(1, 1.75, 1);
    }

    function Header() {
        $this->header->getBody($this);
    }

    function Footer() {
        $this->footer->getBody($this);
    }
    
    //$scale: porcentaje a escalar la imagen, entre 0 y 1
    //Si scale es != null w y h son ignorados
    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $scale = null, $link=''){
        //Calculamos los dpi para reescalar la imagen. El default es -96
        if($scale != null){
            $dpi = -96-96*(1-$scale);
            $dpi = ($dpi>0) ? 0 : $dpi;
            
            parent::Image($file,$x,$y,$dpi,$dpi,$type,$link);
        }
        else{
            parent::Image($file,$x,$y,$w,$h,$type,$link);
        }
    }

    /*
     * Formatos de texto:
     */
    function titulo1F(){
        $this->SetFont('calibri', 'B', 14);
        $this->SetTextColor(54,95,145);
        $this->SetDrawColor(54,95,145);
    }
    
    function titulo2F(){
        $this->SetFont('calibri', '', 14);
        $this->SetTextColor(79,129,189);
        $this->SetDrawColor(79,129,189);
    }
    
    function titulo3F(){
        $this->SetFont('calibri','B',12);
        $this->SetTextColor(0,0,0);
        $this->SetDrawColor(0,0,0);
    }
    
    function mainTextF(){
        $this->SetFont('calibri','',12);
        $this->SetTextColor(0,0,0);
        $this->SetDrawColor(0,0,0);
    }
}