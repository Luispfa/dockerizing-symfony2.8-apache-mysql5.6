<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

class DocumentoPDF {
    public function Output($header, $footer, $pages = array()){
        //sfConfig::set('sf_web_debug', false);
        
        $pdf   = new PDF($header, $footer, "P", "in");
        
        foreach($pages as $page){
            if($page instanceof Page){
                $pdf->addPage();
                $page->getBody($pdf);
            }
        }
        
        $pdf->Output('documento.pdf','I');
    }
    
    public function OutputToFile($filename, $header, $footer, $pages = array()){
        //sfConfig::set('sf_web_debug', false);
        
        $pdf   = new PDF($header, $footer, "P", "in");
        
        foreach($pages as $page){
            if($page instanceof Page){
                $pdf->addPage();
                $page->getBody($pdf);
            }
        }
        
        $pdf->Output($filename.'.pdf','F');
    }
}