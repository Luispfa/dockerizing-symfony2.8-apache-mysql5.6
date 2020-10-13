<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use DateTime;

use Lugh\DbConnectionBundle\Lib\Classes\PDF\Site;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\zebraTable;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;

class UsoPageWeekly extends PageContainer implements Page{
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
    
    function __construct($servicio, $siteId = null){ 
        
        $platformDates = json_decode($this->getContainer()->get('lugh.parameters')->getByKey('Platform.time.activate', ''), true);
        if(isset($platformDates['from'])){
            list($day, $month, $year, $hour, $minute, $second) = sscanf($platformDates['from'], "%d-%d-%d %d:%d:%d");
            $this->startDate = mktime($hour, $minute, $second, $month, $day, $year);
        } else {
            $date = (new \DateTime())->sub(date_interval_create_from_date_string('1 month'));
            list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
            $this->startDate = mktime($hour, $minute, $second, $month, $day, $year);
        }
        if(isset($platformDates['to'])){
            list($day, $month, $year, $hour, $minute, $second) = sscanf($platformDates['to'], "%d-%d-%d %d:%d:%d");
            $this->endDate = mktime($hour, $minute, $second, $month, $day, $year);
        } else {
            $date = new \DateTime();
            list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
            $this->endDate = mktime($hour, $minute, $second, $month, $day, $year);
        }
        
        $cliente        = $this->getContainer()->get('lugh.parameters')->getByKey('Config.customer.title', '');
        
        $this->cliente = ($cliente == null) ? $cliente : $cliente;
        
        $this->siteId   = ($siteId == null) ? $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.site_id', '') : $siteId;
        $this->baseSite = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.address', '');
        $this->apiKey   = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.key', '');        
        
        $sDate = new DateTime('6 days ago');
        $sDate->setTime(0,0);
        
        if($sDate < new DateTime($platformDates['from'])){
            $sDate = new DateTime($platformDates['from']);
        }

        $eDate = new DateTime();
        
        if($eDate > new DateTime($platformDates['to'])){
            $eDate = new DateTime($platformDates['to']);
        }

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

        $this->servicio = "la plataforma electrónica";

    }
    
    
    public function getBody($pdf){
        
        
        $sDate = new DateTime('6 days ago');

        $eDate = new DateTime();
        
        
        //Last week stats
        if($this->startDate < strtotime($sDate->format('d-m-Y'))){
            
            $sDateLastWeek = new DateTime('13 days ago');
            $eDateLastWeek = new DateTime('7 days ago');
            
            $siteLastWeek = new Site($this->baseSite,$this->apiKey,$this->siteId, $sDateLastWeek, $eDateLastWeek);
            $parsedInfoLastWeek = $siteLastWeek->GetAndParseVisitsInfo();
            $referrersLastWeek  = $parsedInfoLastWeek['referrers'];
            $visitsLastWeek     = $parsedInfoLastWeek['visits'];

            foreach($referrersLastWeek as $key => $value){
                if($referrersLastWeek[$key] == null){ $referrersLastWeek[$key] = 0; }
            }
            foreach($visitsLastWeek as $key => $value){
                if($visitsLastWeek[$key] == null){ $visitsLastWeek[$key] = 0; }
            }
            
        }
        
        $visits   = $this->visits;
        $referrers= $this->referrers;
        
        $zTable = new zebraTable();
        $y = $pdf->GetY();

        $pdf->titulo1F();
        $pdf->write(.3,utf8_decode("Uso semanal de ".$this->servicio));

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
                $this->cliente ." se puso en marcha el día ". date("d/m/Y g:i",$this->startDate);

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
                " y se detuvo el día ". date("d/m/Y g:i",$this->endDate) . ", por lo que la plataforma se encontró operativa durante ". $dias . " días y ". $horas . " horas";
        }
        $visitsCompare = '';
        if(isset($visitsLastWeek)){
            if ($visits['unicas'] == $visitsLastWeek['unicas']){
                $visitsCompare = " (el mismo número que la semana anterior) ";
            }
            else if($visits['unicas'] < $visitsLastWeek['unicas']){
                $visitsCompare = " (".strval($visitsLastWeek['unicas'] - $visits['unicas']) ." visitas únicas menos que la semana anterior) ";
            }
            else if($visits['unicas'] > $visitsLastWeek['unicas']){
                $visitsCompare = " (".strval($visits['unicas'] - $visitsLastWeek['unicas']) ." visitas únicas más que la semana anterior) ";
            }
        }
        
        $statement .= 
                ". A continuación se presentan los datos relevantes de navegación:\n\n"
               ."Durante la semana del ".$sDate->format('d-m-y')." al ".$eDate->format('d-m-y')." se han recibido ". $visits['unicas'] ." visitas únicas".$visitsCompare.", las cuales han llegado ";

        if(strcmp($this->servicio,'la plataforma electrónica') == 0){
            $statement .= "a la plataforma electrónica";
        } else {
            $statement .= "al " . $this->servicio;
        }

        $statement .= " mediante los siguientes enlaces:";

        $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode($statement),0,'J');

        //Tabla 1
        $pdf->setY($pdf->GetY()+0.2);
        $cellWidth = ($pdf->w-$pdf->lMargin-$pdf->rMargin)*0.3;

        $zTable->titleCell($pdf, "Enlace", $cellWidth);
        $zTable->titleCell($pdf, "Número", $cellWidth);
        $zTable->titleCell($pdf, "Semana anterior", $cellWidth);
        
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Página web externa", $cellWidth);
        $zTable->rowCell($pdf, $referrers['paginaExterna'], $cellWidth);
        $zTable->rowCell($pdf, $referrersLastWeek['paginaExterna'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Motor de Búsqueda", $cellWidth);
        $zTable->rowCell($pdf, $referrers['buscador'], $cellWidth);
        $zTable->rowCell($pdf, $referrersLastWeek['buscador'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Directo", $cellWidth);
        $zTable->rowCell($pdf, $referrers['directo'], $cellWidth);
        $zTable->rowCell($pdf, $referrersLastWeek['directo'], $cellWidth);

        //Texto 2
        $pdf->setY($pdf->GetY()+0.4);
        $pdf->mainTextF();

        
        $visitsCompareRetornos = '';
        if(isset($visitsLastWeek)){
            if ($visits['retornos'] == $visitsLastWeek['retornos']){
                $visitsCompareRetornos = " (el mismo número que la semana anterior) ";
            }
            else if($visits['retornos'] < $visitsLastWeek['retornos']){
                $visitsCompareRetornos = " (".strval($visitsLastWeek['retornos'] - $visits['retornos']) ." menos que la semana anterior) ";
            }
            else if($visits['retornos'] > $visitsLastWeek['retornos']){
                $visitsCompareRetornos = " (".strval($visits['retornos'] - $visitsLastWeek['retornos']) ." más que la semana anterior) ";
            }
        }
        if(strcmp($this->servicio,'la plataforma electrónica') == 0){
            $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(
                "Además de las ". $visits['unicas'] ." visitas únicas se han recibido otras ". $visits['retornos'] ." visitas de usuarios que ya habían entrado a la plataforma electrónica con anterioridad ".$visitsCompareRetornos.".")
               ,0,'J');
        } else {
            $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(
                "Además de las ". $visits['unicas'] ." visitas únicas se han recibido otras ". $visits['retornos'] ." visitas de usuarios que ya habían entrado al ".$this->servicio." con anterioridad ".$visitsCompareRetornos.".")
               ,0,'J');
        }

        //Tabla 2
        $pdf->setY($pdf->GetY()+0.2);

        $zTable->titleCell($pdf, "Tipo visita", $cellWidth);
        $zTable->titleCell($pdf, "Número", $cellWidth);
        $zTable->titleCell($pdf, "Semana anterior", $cellWidth);

        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Únicas", $cellWidth);
        $zTable->rowCell($pdf, ($visits['unicas'] == null) ? 0:$visits['unicas'], $cellWidth);
        $zTable->rowCell($pdf, ($visitsLastWeek['unicas'] == null) ? 0:$visitsLastWeek['unicas'], $cellWidth);
        $zTable->newRow($pdf);
        $zTable->rowCell($pdf, "Totales", $cellWidth);
        $zTable->rowCell($pdf, $visitsLastWeek['unicas']+$visitsLastWeek['retornos'], $cellWidth);
        $zTable->rowCell($pdf, $visitsLastWeek['unicas']+$visitsLastWeek['retornos'], $cellWidth);

        //Texto 3
        if($visits['unicas']+$visits['retornos'] > 0 && $visits['paises'] != 0){
            $pdf->setY($pdf->GetY()+0.4);
            $pdf->mainTextF();
            $pdf->Write(.25,utf8_decode(
                    "Las visitas se han producido desde las siguientes localizaciones:\n")
                    );

            //Tabla 3
            $pdf->setY($pdf->GetY()+0.2);
            $zTable->titleCell($pdf, "País", $cellWidth);
            $zTable->titleCell($pdf, "Número (Visitas únicas)", $cellWidth);
            $zTable->titleCell($pdf, "Semana anterior", $cellWidth);

            
            arsort($visits['paises']);//Ordenamos los paises de más visitas a menos
            $i     = 0;
            $otros = 0;
            foreach($visits['paises'] as $key => $val) {
                if($i < 10){          //Insertamos los 10 primeros países
                    $zTable->newRow($pdf);
                    $zTable->rowCell($pdf, $key, $cellWidth);
                    $zTable->rowCell($pdf, $val, $cellWidth);
                    if(isset($visitsLastWeek['paises'][$key])){
                        $zTable->rowCell($pdf, $visitsLastWeek['paises'][$key], $cellWidth);
                    }
                    else{
                        $zTable->rowCell($pdf, 0, $cellWidth);
                    }
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
