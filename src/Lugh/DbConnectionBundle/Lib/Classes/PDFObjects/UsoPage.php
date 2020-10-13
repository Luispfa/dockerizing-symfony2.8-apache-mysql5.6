<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\Classes\PDF\Site;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\zebraTable;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;

class UsoPage extends PageContainer implements Page{
    var $site;
    
    var $baseSite;
    var $apiKey;
    var $siteId;
    
    var $startDate;
    var $endDate;
    var $cliente;
    
    var $parsedInfo;
    var $referrers;
    var $visits;
    
    var $servicio;
    
    function __construct($servicio, $siteId = null, $startDate = null, $endDate = null, $cliente = null){ 
        $platformDates = json_decode($this->getContainer()->get('lugh.parameters')->getByKey('Platform.time.activate', ''), true);
        $client        = $this->getContainer()->get('lugh.parameters')->getByKey('Config.customer.title', '');

        if($startDate == null){
            if(isset($platformDates['from'])){
                list($day, $month, $year, $hour, $minute, $second) = sscanf($platformDates['from'], "%d-%d-%d %d:%d:%d");
                $this->startDate = mktime($hour, $minute, $second, $month, $day, $year);
            } else {
                $date = (new \DateTime())->sub(date_interval_create_from_date_string('1 month'));
                list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
                $this->startDate = mktime($hour, $minute, $second, $month, $day, $year);
            }
        }
        else{
            list($day, $month, $year) = sscanf($startDate,"%d/%d/%d");
            $this->startDate = mktime(0,0,0,$month,$day,$year);
        }
        
        if($endDate == null){
            if(isset($platformDates['to'])){
                list($day, $month, $year, $hour, $minute, $second) = sscanf($platformDates['to'], "%d-%d-%d %d:%d:%d");
                $this->endDate = mktime($hour, $minute, $second, $month, $day, $year);
            } else {
                $date = new \DateTime();
                list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
                $this->endDate = mktime($hour, $minute, $second, $month, $day, $year);
            }
        }
        else{
            list($day, $month, $year) = sscanf($endDate,"%d/%d/%d");
            $this->endDate = mktime(0,0,0,$month,$day,$year);
        }
        
        $this->cliente = ($cliente == null) ? $client : $cliente;
        
        $this->siteId   = ($siteId == null) ? $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.site_id', '') : $siteId;
        $this->baseSite = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.address', '');
        $this->apiKey   = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.key', '');        
        
        $sDate = new \DateTime();
        $eDate = new \DateTime();
        
        $sDate->setTimestamp($this->startDate);
        $eDate->setTimestamp($this->endDate);
        
        $this->site = new Site($this->baseSite,$this->apiKey,$this->siteId, $sDate, $eDate);
        $this->parsedInfo = $this->site->GetAndParseVisitsInfo();
        $this->referrers  = $this->parsedInfo['referrers'];
        $this->visits     = $this->parsedInfo['visits'];
        
        foreach($this->referrers as $key => $value){
            if($this->referrers[$key] == null){ $this->referrers[$key] = 0; }
        }
        foreach($this->visits as $key => $value){
            if($this->visits[$key] == null){ $this->visits[$key] = 0; }
        }
        
        $this->servicio = $servicio;
        if(strcmp($this->servicio,'') == 0){
            $this->servicio = "la plataforma electrónica";
        }
    }
    
    
    public function getBody($pdf){
        $visits   = $this->visits;
        $referrers= $this->referrers;
        
        $zTable = new zebraTable();
        $y = $pdf->GetY();

        $pdf->titulo1F();
        $pdf->write(.3,utf8_decode("Uso de ".$this->servicio));

        $pdf->setY($y+=.3);
        $pdf->titulo2F();
        $pdf->write(.3,utf8_decode("Estadísticas web"));

        //Texto 1
        $pdf->setY($y+=.3);
        $pdf->mainTextF();

        if(strcmp($this->servicio,'la plataforma electrónica') == 0){
            $statement = "La plataforma electrónica de ";
        } else {
            $statement = "La plataforma de " . $this->servicio . " de ";
        }

        $statement .= 
                $this->cliente ." se puso en marcha el día ". date("d/m/Y G:i",$this->startDate);

        if(strtotime('now') < $this->endDate){
            $diff = strtotime('now') - $this->startDate;
            $dias = floor($diff / (60 * 60 * 24));
            $horas = floor($diff / (60 * 60)) - ($dias *24);

            $statement .=
                " y continúa activa en estos momentos, por lo que la plataforma lleva operativa ". $dias . " días y ". $horas . " horas";
        }
        if(strtotime('now') > $this->endDate){
            $diff = $this->endDate - $this->startDate;
            $dias = floor($diff / (60 * 60 * 24));
            $horas = floor($diff / (60 * 60)) - ($dias *24);
            $statement .=
                " y se detuvo el día ". date("d/m/Y G:i",$this->endDate) . ", por lo que la plataforma se encontró operativa durante ". $dias . " días y ". $horas . " horas";
        }
        $statement .= 
                ". A continuación se presentan los datos relevantes de navegación:\n\n"
               ."Durante este periodo de tiempo se han recibido ". $visits['unicas'] ." visitas únicas, las cuales han llegado ";

        if(strcmp($this->servicio,'la plataforma electrónica') == 0){
            $statement .= "a la plataforma electrónica";
        } else {
            $statement .= "al " . $this->servicio;
        }

        $statement .= " mediante los siguientes enlaces:";

        $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode($statement),0,'J');

        //Tabla 1
        $pdf->setY($pdf->GetY()+0.2);
        $cellWidth = ($pdf->w-$pdf->lMargin-$pdf->rMargin)*0.5;

        $zTable->titleCell($pdf, "Enlace", $cellWidth);
        $zTable->titleCell($pdf, "Número", $cellWidth);
        
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Página web externa", $cellWidth);
        $zTable->rowCell($pdf, $referrers['paginaExterna'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Motor de Búsqueda", $cellWidth);
        $zTable->rowCell($pdf, $referrers['buscador'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Directo", $cellWidth);
        $zTable->rowCell($pdf, $referrers['directo'], $cellWidth);

        //Texto 2
        $pdf->setY($pdf->GetY()+0.4);
        $pdf->mainTextF();

        if(strcmp($this->servicio,'la plataforma electrónica') == 0){
            $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(
                "Además de las ". $visits['unicas'] ." visitas únicas se han recibido otras ". $visits['retornos'] ." visitas de usuarios que ya habían entrado a la plataforma electrónica con anterioridad.")
               ,0,'J');
        } else {
            $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(
                "Además de las ". $visits['unicas'] ." visitas únicas se han recibido otras ". $visits['retornos'] ." visitas de usuarios que ya habían entrado al ".$this->servicio." con anterioridad.")
               ,0,'J');
        }

        //Tabla 2
        $pdf->setY($pdf->GetY()+0.2);

        $zTable->titleCell($pdf, "Tipo visita", $cellWidth);
        $zTable->titleCell($pdf, "Número", $cellWidth);

        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Únicas", $cellWidth);
        $zTable->rowCell($pdf, ($visits['unicas'] == null) ? 0:$visits['unicas'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Totales", $cellWidth);
        $zTable->rowCell($pdf, $visits['unicas']+$visits['retornos'], $cellWidth);

        //Texto 3
        if($visits['unicas']+$visits['retornos'] > 0){
            $pdf->setY($pdf->GetY()+0.4);
            $pdf->mainTextF();
            $pdf->Write(.25,utf8_decode(
                    "Las visitas se han producido desde las siguientes localizaciones:\n")
                    );

            //Tabla 3
            $pdf->setY($pdf->GetY()+0.2);
            $zTable->titleCell($pdf, "País", $cellWidth);
            $zTable->titleCell($pdf, "Número (Visitas únicas)", $cellWidth);

            arsort($visits['paises']);//Ordenamos los paises de más visitas a menos
            $i     = 0;
            $otros = 0;
            foreach($visits['paises'] as $key => $val) {
                if($i < 10){          //Insertamos los 10 primeros países
                    $zTable->newRow($pdf);
                    $zTable->rowCell($pdf, $key, $cellWidth);
                    $zTable->rowCell($pdf, $val, $cellWidth);
                }
                else{                 //Y contamos el total de visitas del resto
                    $otros += $val;
                }
                $i++;
            }

            if($i>=10){ //Si habían más de 10 países, insertamos la suma del resto
                $zTable->newRow($pdf);
                $zTable->rowCell($pdf, 'Otros', $cellWidth);
                $zTable->rowCell($pdf, $otros, $cellWidth);
            }
        }

        //**************Página 2
        $pdf->AddPage();
        $pdf->Write(0.25,  utf8_decode("Visitas por día:"));
        

        $pdf->setY($pdf->GetY()+0.2);
        $pdf->Image($this->site->GetVisitDayGraphImagePngPath(), null, null, 0, 0, "PNG", 0.75);

        $pdf->setY($pdf->GetY()+0.5);
        $pdf->Write(.25,utf8_decode("Visitas por hora"));

        $pdf->setY($pdf->GetY()+0.2);
        $pdf->Image($this->site->GetVisitHourGraphImagePngPath(), null, null, 0, 0, "PNG");

        $pdf->setY($pdf->GetY()+0.5);
        $pdf->titulo3F();
        $pdf->Write(.25,utf8_decode("Nota:\n"));
        $pdf->mainTextF();
        $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(
                "En las gráficas también están contempladas las entradas del usuario administrador.")
               ,0,'J');
    }
}
