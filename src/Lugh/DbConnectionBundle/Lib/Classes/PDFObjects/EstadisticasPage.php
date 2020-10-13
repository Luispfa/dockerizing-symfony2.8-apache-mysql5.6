<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Site;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\zebraTable;

class EstadisticasPage extends PageContainer implements Page{
    
    var $apps;
    function __construct($apps, $comparativa, $startDate = null, $endDate = null){
        $this->apps         = $apps;
        $this->comparativa  = $comparativa;
        
        $platformDates = json_decode($this->getContainer()->get('lugh.parameters')->getByKey('Platform.time.activate', ''), true);

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
        
        $sDate = new \DateTime();
        $eDate = new \DateTime();
        
        $sDate->setTimestamp($this->startDate);
        $eDate->setTimestamp($this->endDate);
        
        $this->siteId   = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.site_id', '');
        $this->baseSite = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.address', '');
        $this->apiKey   = $this->getContainer()->get('lugh.parameters')->getByKey('stats.api.key', '');  
        
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
    }
    
    private function getSDerecho($nHilos,$nHilosResp){
        $sentence = '';
        if($nHilos > 1){
            $sentence.= "-  Se han creado ". $nHilos ." hilos, de los cuales, ha";
            if($nHilosResp>1 || $nHilosResp == 0){
                $sentence .= 'n';
            }
            $sentence.= " recibido respuesta " . $nHilosResp;
        }
        elseif($nHilos == 1){
            $sentence.= "-  Se ha creado ". $nHilos ." hilo, el cual ";
            $sentence .= ($nHilosResp==1) ? 'ha' : 'no ha';
            $sentence.= " recibido respuesta " . $nHilosResp;
        }
        elseif($nHilos == 0){
            $sentence.= "- No se ha creado ningún hilo.";
        }
        return $sentence;
    }

    private function getS($nAccionistas, $nAcciones, $pluralA, $singularA, $estadistica){
        $sentence = '';
        if($nAccionistas == null) $nAccionistas = 0;
        if($nAcciones    == null) $nAcciones    = 0;
        
        if($nAccionistas != 0){
            $sentence.= "-  ". $nAccionistas ." accionista";
            $sentence.= ($nAccionistas == 1) ? $singularA : $pluralA;
            $sentence.= $estadistica;
            $sentence.= $nAcciones;
            $sentence.= ($nAcciones == 1) ? ' acción' : ' acciones';
            $sentence .= ".\n";
        }
        return $sentence;
    }
    
    private function getSByStateForo($data, $pluralS, $singularS, $onlyOne = false){
        $sentence = '';
        if($data != null && $data['count'] > 0){
            $sentence .= '     >  ';
            if($onlyOne){
                $sentence .= 'Está';
            }
            else{
                $sentence .= $data['count'];
                $sentence .= ' está';
            }
            if($data['count'] == 1){
                $sentence .= ' ';
                $sentence .= $singularS;
            }
            else{
                $sentence .= 'n ';
                $sentence .= $pluralS;
            }
            $sentence .= ' (';
            $sentence .= $data['acciones'];
            $sentence .= ($data['acciones'] == 1) ? ' acción)' : ' acciones)';
            $sentence .= ".\n";
        }
        return $sentence;
    }
    
    private function getSBForo($data, $pluralA, $singularA, $gen = 'fem'){
        $n = $data['total']['count'];
        $sentence = '-  ';
        
        if($n == 0){
            $sentence .= ($gen == 'fem') ? 'Ninguna' : 'Ningún';
        }
        else{
            $sentence .= $data['total']['count'];
        }
        
        $sentence .= ' ';
        $sentence .= ($n == 1 || $n == 0) ? $singularA : $pluralA;
        $sentence .= ($gen == 'fem') ? ' presentada' : ' presentado';
        
        if($n >  1) $sentence .= "s,";
        if($n == 1) $sentence .= ",";
        if($n == 0) $sentence .= ".\n";
        
        if($n >= 1){
            $onlyOne = false;
            if($n > 1){
                $sentence .= ' de ';
                $sentence .= ($gen == 'fem') ? 'las ' : 'los ';
                $sentence .= 'cuales:';
                
            }
            else if($n == 1){
                $onlyOne = true;
                $sentence .= ($gen == 'fem') ? ' la ' : ' el ';
                $sentence .= 'cual:';
            }
            
            $sentence .= "\n";
            if($gen=='fem'){
                $sentence .= $this->getSByStateForo($data['approved'], 'aprobadas',  'aprobada'  ,$onlyOne);
                $sentence .= $this->getSByStateForo($data['discarded'],'descartadas','descartada',$onlyOne);
                $sentence .= $this->getSByStateForo($data['confirmed'],'confirmadas','confirmada',$onlyOne);
            }
            else{
                $sentence .= $this->getSByStateForo($data['approved'], 'aprobados',  'aprobado'  ,$onlyOne);
                $sentence .= $this->getSByStateForo($data['discarded'],'descartados','descartado',$onlyOne);
                $sentence .= $this->getSByStateForo($data['confirmed'],'confirmados','confirmado',$onlyOne);
            }
            
            //Válido para ambos géneros:
            $sentence .= $this->getSByStateForo($data['pending'],    'pendientes de aprobación','pendiente de aprobación');
            $sentence .= $this->getSByStateForo($data['rev_pending'],'pendientes de revisión',  'pendiente de revisión');
        }
        return $sentence;
    }
    
    private function buildItemTable($pdf, $items, $item_v, $adh_v, $item_s, $width, $bMargin = 1.5){       
        $pdf->SetFont('calibri','B',12);
        $pdf->SetTextColor(118,146,60);
        $pdf->SetDrawColor(118,146,60);
        $pdf->setFillColor(255,255,255);
        
        foreach($items[$item_v] as $item){
            //Calculamos las líneas que ocupa la fila
            if(sizeof($item->getAdhesions()) > 2){
                $lines = sizeof($item[$adh_v]);
            }
            else{
                $lines = 2;
            }
            
            //Calculamos si la fila se saldrá se la página (y si es así saltamos página)
            if(.25*$lines + $pdf->y > $pdf->h - $bMargin){
                $pdf->addPage();
                $pdf->y = $pdf->tMargin;
            }
            
            //Dibujamos la fila:
            $pdf->setX($pdf->lMargin);
            $st_y   = $pdf->y;
            //$pdf->Multicell($width,.25, utf8_decode(htmlspecialchars_decode($item_s)),'T','L',true);
            $pdf->Multicell($width,.25, utf8_decode(html_entity_decode($item_s, ENT_QUOTES)),'T','L',true);
            
            $pdf->x += $width;
            $pdf->y  = $st_y;
            $title = $item->getTitle() . "\nAutor: " . $item->getAutor()->getName();
            //$pdf->Multicell($width,.25, utf8_decode(htmlspecialchars_decode($title)),'T','L',true);
            $pdf->Multicell($width,.25, utf8_decode(html_entity_decode($title, ENT_QUOTES)),'T','L',true);
            
            $pdf->x = $pdf->w - $pdf->lMargin - $width;
            $adhs = "";
            
            $len = count($item->getAdhesions());
            $i = 0;
            foreach($item->getAdhesions() as $adhesion){
                $i++;
                
                $adhs .= $adhesion->getAccionista()->getName();
                $adhs .= " (";
                $adhs .= $adhesion->getAccionista()->getSharesNum();
                $adhs .= ($adhesion->getAccionista()->getSharesNum() == 1) ? " acción" : " acciones";
                $adhs .= ")";
                
                if($i < $len) $adhs .= "\n";
            }
            
            $pdf->y  = $st_y;
            $pdf->MultiCell($width,.25,  utf8_decode($adhs),'T','L',true);
            
            if($len < 2){
                $pdf->y += 0.25;
            }
        }
    }

    public function numItems($items){
        $count['total']       = array('count' => count($items), 'acciones' => 0);
        $count['approved']    = array('count' => 0,             'acciones' => 0);
        $count['discarded']   = array('count' => 0,             'acciones' => 0);
        $count['confirmed']   = array('count' => 0,             'acciones' => 0);
        $count['pending']     = array('count' => 0,             'acciones' => 0);
        $count['rev_pending'] = array('count' => 0,             'acciones' => 0);
        foreach($items as $item){
            $count['total']['acciones'] += $item->getAutor()->getSharesNum();
            switch($item->getState()){
                case 1: //pending
                    $count['pending']    ['count']++;
                    $count['pending']    ['acciones'] += $item->getAutor()->getSharesNum();
                    break;
                case 2: //public
                    $count['approved']   ['count']++;
                    $count['approved']   ['acciones'] += $item->getAutor()->getSharesNum();
                    break;
                case 3: //retornate
                    $count['rev_pending']['count']++;
                    $count['rev_pending']['acciones'] += $item->getAutor()->getSharesNum();
                    break;
                case 4: //reject
                    $count['discarded']  ['count']++;
                    $count['discarded']  ['acciones'] += $item->getAutor()->getSharesNum();
                    break;
            }
        }
        return $count;
    }

    public function readDelegaciones($data){
        /* consejo/persona con/sin intención */
        $delegaciones['consejoCon'] = array('count' => 0, 'acciones' => 0);
        $delegaciones['consejoSin'] = array('count' => 0, 'acciones' => 0);
        $delegaciones['personaCon'] = array('count' => 0, 'acciones' => 0);
        $delegaciones['personaSin'] = array('count' => 0, 'acciones' => 0);

        foreach($data as $delegacion){
            if($delegacion->getDelegado()->getIsConseller() && count($delegacion->getVotacion()) == 0){ //Consejo, Sin intención
                $delegaciones['consejoSin']['count']++;
                $delegaciones['consejoSin']['acciones'] += $delegacion->getAccionista()->getSharesNum();
                continue;
            }
            if($delegacion->getDelegado()->getIsConseller() && count($delegacion->getVotacion()) > 0){ //Consejo, Con intención
                $delegaciones['consejoCon']['count']++;
                $delegaciones['consejoCon']['acciones'] += $delegacion->getAccionista()->getSharesNum();
                continue;
            }
            if(!$delegacion->getDelegado()->getIsConseller() && count($delegacion->getVotacion()) > 0){ //Persona, Sin intención
                $delegaciones['personaCon']['count']++;
                $delegaciones['personaCon']['acciones'] += $delegacion->getAccionista()->getSharesNum();
                continue;
            }
            if(!$delegacion->getDelegado()->getIsConseller() && count($delegacion->getVotacion()) == 0){ //Persona, Sin intención
                $delegaciones['personaSin']['count']++;
                $delegaciones['personaSin']['acciones'] += $delegacion->getAccionista()->getSharesNum();
                continue;
            }
        }
        return $delegaciones;
    }
    
    public function getBody($pdf){
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->clear();
        $storage    = $this->getContainer()->get('lugh.server')->getStorage();
        //$apps       = json_decode($this->getContainer()->get('lugh.parameters')->getByKey('Accionista.default.apps'),true);

        $pdf->titulo2F();
        
        $pdf->Write(.3,  utf8_decode("Estadísticas de la aplicación"));
        $pdf->setY($pdf->GetY()+.3);
        
        $pdf->mainTextF();
        $pdf->Write(.25,utf8_decode("A continación se presentan los datos relevantes de la aplicación:\n"));
        $pdf->setX($pdf->GetX()+.3);
        
        //Extraemos la información
        try {
            $acciones     = $storage->getLastAccions();
            $accionistas  = $storage->getAccionistas();
            $proposals    = $storage->getProposals();
            $initiatives  = $storage->getInitiatives();
            $offers       = $storage->getOffers();
            $requests     = $storage->getRequests();
            $delegaciones = $storage->getLastDelegaciones();
            $votosS       = $storage->getLastVotos();
            $threads      = $storage->getThreads();
        } catch (Exception $ex){
            die('error getBody EstadisticasPageForo: ' . $ex);
        }

        $accionesTotales = 0;
        $accionistasSinAccion = array('count' => 0, 'acciones' => 0);
        foreach($accionistas as $key => $accionista){
            $accionesTotales += $accionista->getSharesNum();
            if(count($accionista->getAccion()) == 0){
                $accionistasSinAccion['count'] ++;
                $accionistasSinAccion['acciones'] += $accionista->getSharesNum();
            }
        }

        /*
         * GENERAL
         */
        $accRegistrados = count($accionistas);

        /*
         * FORO
         */
        $propuestas     = $this->numItems($proposals);
        $iniciativas    = $this->numItems($initiatives);
        $ofertas        = $this->numItems($offers);
        $peticiones     = $this->numItems($requests);

        /*
         * VOTO
         */
        $delegaciones = $this->readDelegaciones($delegaciones);
        $votos = array('count' => 0, 'acciones' => 0);
        $votos['count'] = count($votosS);
        foreach($votosS as $voto){
            $votos['acciones'] += $voto->getSharesNum();
        }
        /*foreach($acciones as $accion){
            if(strcmp($accion->nameClass,'Voto') === 0){
                $votos['count']++;
                $votos['acciones'] += $accion->getSharesNum();
            }
        }*/

        /*
         * DERECHO
         */
        $nHilos = count($threads);
        $nHilosResp = 0;
        foreach($threads as $thread){
            $messages = $thread->getMessages();
            foreach($messages as $message){
                if($message->getAutor()->isAdmin()){
                    $nHilosResp++;
                    break;
                }
            }
        }
        
        //La ordenamos
        $sentence = "";
        $sentence.= $this->getS($accRegistrados,$accionesTotales, 's se han', ' se ha', ' dado de alta, con un total de ');
        $sentence.= $this->getS($accionistasSinAccion['count'],$accionistasSinAccion['acciones'],  's no han', ' no ha', ' registrado ninguna acción, con un total de ');
        //$sentence.= $this->getS($votedAll[0],   $votedAll[1],     's han',    ' ha',    ' votado en todos los puntos, con un total de ');

        /*
         * FORO
         */
        if($this->apps['foro'] == 1){
            $sentence .= $this->getSBForo($propuestas, 'propuestas', 'propuesta');
            $sentence .= $this->getSBForo($iniciativas,'iniciativas','iniciativa');
            $sentence .= $this->getSBForo($ofertas,    'ofertas de representación',    'oferta de representación');
            $sentence .= $this->getSBForo($peticiones, 'peticiones de representación', 'petición de representación');
        }

        /*
         * Voto
         */
        if($this->apps['voto'] == 1){
            $sentence.= $this->getS($votos['count'],                      $votos['acciones'],                      's han', ' ha',  ' registrado una votación, con un total de ');
            $sentence.= $this->getS($delegaciones['consejoCon']['count'], $delegaciones['consejoCon']['acciones'], 's han', ' ha',  ' delegado en el consejo de administración con intenciones de voto, con un total de ');
            $sentence.= $this->getS($delegaciones['personaCon']['count'], $delegaciones['personaCon']['acciones'], 's han', ' ha',  ' delegado en una persona con intenciones de voto, con un total de ');
            $sentence.= $this->getS($delegaciones['consejoSin']['count'], $delegaciones['consejoSin']['acciones'], 's han', ' ha',  ' delegado en el consejo de administración sin intenciones de voto, con un total de ');
            $sentence.= $this->getS($delegaciones['personaSin']['count'], $delegaciones['personaSin']['acciones'], 's han', ' ha',  ' delegado en una persona sin intenciones de voto, con un total de ');
        }
        /*
         * DERECHO
         */
        if($this->apps['derecho'] == 1){
            $sentence.= $this->getSDerecho($nHilos,$nHilosResp);
        }
        
        //Y la presentamos
        $pdf->MultiCell(($pdf->w-$pdf->lMargin-$pdf->rMargin),.25,utf8_decode(htmlspecialchars_decode($sentence)),0,'J');
        
        //Página de tabla:
        $items['proposal_by_item']   = $proposals;
        $items['initiative_by_item'] = $initiatives;
        $items['offer_by_item']      = $offers;
        $items['request_by_item']    = $requests;


        if(empty($items['proposal_by_item'])  && 
           empty($items['initiative_by_item'])&&
           empty($items['offer_by_item'])     &&
           empty($items['request_by_item'])){
            $items = 'empty';
        }

        if($items != 'empty'){
            $pdf->addPage();
            $pdf->bMargin = $pdf->tMargin+20;
            $pdf->setY($pdf->tMargin);
            
            $pdf->titulo2F();
            $pdf->Write(.3,  utf8_decode("Lista de elementos"));
            
            $pdf->mainTextF();
            $pdf->setY($pdf->GetY()+.3);
            
            $z     = new zebraTable();
            $width = ($pdf->w - $pdf->lMargin - $pdf->rMargin) / 3;
            
            $z->titleCell($pdf, "Tipo" ,      $width);
            $z->titleCell($pdf, "Título"    , $width);
            $z->titleCell($pdf, "Adhesiones", $width);

            $pdf->y += .25;
            //Propuestas
            $this->buildItemTable($pdf, $items, "proposal_by_item",  "ProposalAdhesion",  "Propuesta", $width);
            $this->buildItemTable($pdf, $items, "initiative_by_item","InitiativeAdhesion","Iniciativa",$width);
            $this->buildItemTable($pdf, $items, "offer_by_item",     "OfferAdhesion",     "Oferta",    $width);
            $this->buildItemTable($pdf, $items, "request_by_item",   "RequestAdhesion",   "Petición",  $width);

            
        }
        
        //comparativa si se ha indicado base de datos para comparar
        if($this->comparativa != null){
            
            $em->clear();
            
            $pdf->setY($pdf->GetY()+0.2);
            $pdf->titulo2F();
            $pdf->Write(.75,  utf8_decode("Comparativa con plataforma anterior"));
            $pdf->setY($pdf->GetY()+0.5);
            
            $parametrosComparativa = $this->getParametrosComparativa($this->comparativa);
            
            $startTime = null;
            $endTime = null;
            $siteId = null;
            $baseSite = null;
            $apiKey = null;
            
            foreach ($parametrosComparativa as $parameter) {
                if($parameter->getKeyParam() == 'Platform.time.activate'){
                    $timeActivate = json_decode($parameter->getValueParam(), true);
                    if(isset($timeActivate['from'])){
                        list($day, $month, $year, $hour, $minute, $second) = sscanf($timeActivate['from'], "%d-%d-%d %d:%d:%d");
                        $startTime = mktime($hour, $minute, $second, $month, $day, $year);
                    }
                    else{
                        $date = (new \DateTime())->sub(date_interval_create_from_date_string('1 month'));
                        list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
                        $startTime = mktime($hour, $minute, $second, $month, $day, $year);
                    }
                    if(isset($timeActivate['to'])){
                        list($day, $month, $year, $hour, $minute, $second) = sscanf($timeActivate['to'], "%d-%d-%d %d:%d:%d");
                        $endTime = mktime($hour, $minute, $second, $month, $day, $year);
                    }
                    else{
                        $date = (new \DateTime())->sub(date_interval_create_from_date_string('1 month'));
                        list($day, $month, $year, $hour, $minute, $second) = sscanf($date->format('d-m-Y H:i:s'), "%d-%d-%d %d:%d:%d");
                        $endTime = mktime($hour, $minute, $second, $month, $day, $year);
                    }
                }
                elseif($parameter->getKeyParam() == 'stats.api.site_id'){
                    $siteId = $parameter->getValueParam();
                }
                elseif($parameter->getKeyParam() == 'stats.api.address'){
                    $baseSite = $parameter->getValueParam();
                }
                elseif($parameter->getKeyParam() == 'stats.api.key'){
                    $apiKey = $parameter->getValueParam();
                }
            }   
            
            //cálculos de visitas
            $sDate = new \DateTime();
            $eDate = new \DateTime();

            if($startTime != null){
                $sDate->setTimestamp($startTime);
            }
            if($endTime != null){
                $eDate->setTimestamp($endTime);
            }

            $site = new Site($baseSite,$apiKey,$siteId, $sDate, $eDate);
            $parsedInfo = $site->GetAndParseVisitsInfo();
            $visits     = $parsedInfo['visits'];

            foreach($visits as $key => $value){
                if($visits[$key] == null){ $visits[$key] = 0; }
            }
            
            //cálculos de tiempo
            if(strtotime('now') < $this->endDate){
                $diff = strtotime('now') - $this->startDate;
                $dias = floor($diff / (60 * 60 * 24));
                $horas = floor($diff / (60 * 60)) - ($dias *24);
                $tiempoActividadActual = $dias . " días y ". $horas . " horas";
            }
            if(strtotime('now') > $this->endDate){
                $diff = $this->endDate - $this->startDate;
                $dias = floor($diff / (60 * 60 * 24));
                $horas = floor($diff / (60 * 60)) - ($dias *24);
                $tiempoActividadActual = $dias . " días y ". $horas . " horas";
            }
            $diasAbsolutosActividadActual = $diff / (60 * 60 * 24);
            
            if(strtotime('now') < $endTime){
                $diffant = strtotime('now') - $startTime;
                $diasant = floor($diffant / (60 * 60 * 24));
                $horasant = floor($diffant / (60 * 60)) - ($diasant *24);
                $tiempoActividadAnterior = $diasant . " días y ". $horasant . " horas";
            }
            if(strtotime('now') > $endTime){
                $diffant = $endTime - $startTime;
                $diasant = floor($diffant / (60 * 60 * 24));
                $horasant = floor($diffant / (60 * 60)) - ($diasant *24);
                $tiempoActividadAnterior = $diasant . " días y ". $horasant . " horas";
            }
            $diasAbsolutosActividadAnterior = $diffant / (60 * 60 * 24);
            
            //cálculos de accionistas
            $accionistasComparativa = $this->getAccionistasComparativa($this->comparativa);
            $accionesTotalesComparativa = 0;
            $accionistasSinAccionComparativa = array('count' => 0, 'acciones' => 0);
            foreach($accionistasComparativa as $key => $accionistaComparativa){
                $accionesTotalesComparativa += $accionistaComparativa->getSharesNum();
                if(count($accionistaComparativa->getAccion()) == 0){
                    $accionistasSinAccionComparativa['count'] ++;
                    $accionistasSinAccionComparativa['acciones'] += $accionistaComparativa->getSharesNum();
                }
            }
            
            
            $zTable = new zebraTable();
            $pdf->setY($pdf->GetY()+0.2);
            $cellWidth = ($pdf->w-$pdf->lMargin-$pdf->rMargin)*0.3;

            $zTable->titleCell($pdf, "", $cellWidth);
            $zTable->titleCell($pdf, "Plataforma actual", $cellWidth);
            $zTable->titleCell($pdf, "Plataforma anterior", $cellWidth);

            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Tiempo actividad", $cellWidth);
            $zTable->rowCell($pdf, $tiempoActividadActual, $cellWidth);
            $zTable->rowCell($pdf, $tiempoActividadAnterior, $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Visitas únicas", $cellWidth);
            $zTable->rowCell($pdf, $this->visits['unicas'], $cellWidth);
            $zTable->rowCell($pdf, $visits['unicas'], $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Visitas totales", $cellWidth);
            $zTable->rowCell($pdf, $this->visits['unicas'] + $this->visits['retornos'], $cellWidth);
            $zTable->rowCell($pdf, $visits['unicas'] + $visits['retornos'], $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Accionistas de alta", $cellWidth);
            $zTable->rowCell($pdf, $accRegistrados, $cellWidth);
            $zTable->rowCell($pdf, count($accionistasComparativa), $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Acciones", $cellWidth);
            $zTable->rowCell($pdf, $accionesTotales, $cellWidth);
            $zTable->rowCell($pdf, $accionesTotalesComparativa, $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->titleCell($pdf, "Sin realizar ninguna acción:", $cellWidth);
            $zTable->titleCell($pdf, "", $cellWidth);
            $zTable->titleCell($pdf, "", $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Accionistas", $cellWidth);
            $zTable->rowCell($pdf, $accionistasSinAccion['count'], $cellWidth);
            $zTable->rowCell($pdf, $accionistasSinAccionComparativa['count'], $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Acciones", $cellWidth);
            $zTable->rowCell($pdf, $accionistasSinAccion['acciones'], $cellWidth);
            $zTable->rowCell($pdf, $accionistasSinAccionComparativa['acciones'], $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->titleCell($pdf, "Han realizado acciones:", $cellWidth);
            $zTable->titleCell($pdf, "", $cellWidth);
            $zTable->titleCell($pdf, "", $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Accionistas", $cellWidth);
            $zTable->rowCell($pdf, $accRegistrados - $accionistasSinAccion['count'], $cellWidth);
            $zTable->rowCell($pdf, count($accionistasComparativa) - $accionistasSinAccionComparativa['count'], $cellWidth);
            
            $zTable->newRow($pdf);
            $zTable->rowCell($pdf, "Acciones", $cellWidth);
            $zTable->rowCell($pdf, $accionesTotales - $accionistasSinAccion['acciones'], $cellWidth);
            $zTable->rowCell($pdf, $accionesTotalesComparativa - $accionistasSinAccionComparativa['acciones'], $cellWidth);

            $pdf->AddPage();
            
            
            //DIAGRAMAS
            //Diagrama de tiempo de actividad
            // Begin configuration

            $rowLabels = array( "Plataforma Actual", "Plataforma Anterior" );
            $chartXPos = 0.8;
            $chartYPos = 5.5;
            $chartWidth = 6.2;
            $chartHeight = 3.15;
            $chartXLabel = "";
            $chartYLabel = utf8_decode("Días de actividad");
            $chartYStep = 5;

            $chartColours = array(
                              array( 146,208,80 ),
                              array( 79,129,189 )
                            );

            $data = array(
                array($diasAbsolutosActividadActual), array($diasAbsolutosActividadAnterior)       
            );

            // End configuration
            // 
            // 
            // Compute the X scale
            $xScale = count($rowLabels) / ( $chartWidth - 1.6 );

            // Compute the Y scale

            $maxTotal = 0;

            foreach ( $data as $dataRow ) {
              $totalSales = 0;
              foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;
              $maxTotal = ( $totalSales > $maxTotal ) ? $totalSales : $maxTotal;
            }

            $yScale = $maxTotal / $chartHeight;
            
            // Compute the bar width
            $barWidth = ( 1 / $xScale ) / 1.5;
            
            // Add the axes:

            $pdf->SetFont( 'Arial', '', 10 );

            // X axis
            $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + $chartWidth, $chartYPos );

            for ( $i=0; $i < count( $rowLabels ); $i++ ) {
              $pdf->SetXY( $chartXPos + 1.6 +  $i / $xScale, $chartYPos );
              $pdf->Cell( $barWidth, 0.4, $rowLabels[$i], 0, 0, 'C' );
            }

            // Y axis
            $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + 1.18, $chartYPos - $chartHeight - 0.3 );

            for ( $i=0; $i <= $maxTotal; $i += $chartYStep ) {
              $pdf->SetXY( $chartXPos + 0.27, $chartYPos - 0.2 - $i / $yScale );
              $pdf->Cell( 0.79, 0.4, '' . number_format( $i ), 0, 0, 'R' );
              $pdf->Line( $chartXPos + 1.1, $chartYPos - $i / $yScale, $chartXPos + 1.18, $chartYPos - $i / $yScale );
            }
            
            // Add the axis labels
            $pdf->SetFont( 'Arial', 'B', 12 );
            $pdf->SetXY( $chartWidth / 0.87, $chartYPos + 0.35 );
            $pdf->Cell( 1.18, 0.4, $chartXLabel, 0, 0, 'C' );
            $pdf->SetXY( $chartXPos + 0.27, $chartYPos - $chartHeight - 0.47 );
            $pdf->Cell( 0.79, 0.4, $chartYLabel, 0, 0, 'R' );
            
            // Create the bars
            $xPos = $chartXPos + 1.57;
            $bar = 0;
            
            foreach ( $data as $dataRow ) {

                // Total up the sales figures for this product
                $totalSales = 0;
                foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;

                // Create the bar
                $colourIndex = $bar % count( $chartColours );
                $pdf->SetFillColor( $chartColours[$colourIndex][0], $chartColours[$colourIndex][1], $chartColours[$colourIndex][2] );
                $pdf->Rect( $xPos, $chartYPos - ( $totalSales / $yScale ), $barWidth, $totalSales / $yScale, 'DF' );
                $xPos += ( 1 / $xScale );
                $bar++;
            }
            
            
            
            // Diagrama de visitas totales 
            // Begin configuration

            $chartYPos = 9.5;
            $chartYLabel = utf8_decode("Visitas totales");
            $max = max($this->visits['unicas'] + $this->visits['retornos'], $visits['unicas'] + $visits['retornos']);
            $numberOfDigits = strlen((string)$max);
            $precision = 0 - $numberOfDigits + 2;
            $chartYStep = round($max / 8, $precision);

            $data = array(
                array($this->visits['unicas'] + $this->visits['retornos']), array($visits['unicas'] + $visits['retornos'])       
            );
            
            // End configuration
            // 
            // 
            // Compute the X scale
            $xScale = count($rowLabels) / ( $chartWidth - 1.6 );

            // Compute the Y scale

            $maxTotal = 0;

            foreach ( $data as $dataRow ) {
              $totalSales = 0;
              foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;
              $maxTotal = ( $totalSales > $maxTotal ) ? $totalSales : $maxTotal;
            }

            $yScale = $maxTotal / $chartHeight;
            
            // Compute the bar width
            $barWidth = ( 1 / $xScale ) / 1.5;
            
            // Add the axes:

            $pdf->SetFont( 'Arial', '', 10 );

            // X axis
            $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + $chartWidth, $chartYPos );

            for ( $i=0; $i < count( $rowLabels ); $i++ ) {
              $pdf->SetXY( $chartXPos + 1.6 +  $i / $xScale, $chartYPos );
              $pdf->Cell( $barWidth, 0.4, $rowLabels[$i], 0, 0, 'C' );
            }

            // Y axis
            $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + 1.18, $chartYPos - $chartHeight - 0.3 );

            for ( $i=0; $i <= $maxTotal; $i += $chartYStep ) {
              $pdf->SetXY( $chartXPos + 0.27, $chartYPos - 0.2 - $i / $yScale );
              $pdf->Cell( 0.79, 0.4, '' . number_format( $i ), 0, 0, 'R' );
              $pdf->Line( $chartXPos + 1.1, $chartYPos - $i / $yScale, $chartXPos + 1.18, $chartYPos - $i / $yScale );
            }
            
            // Add the axis labels
            $pdf->SetFont( 'Arial', 'B', 12 );
            $pdf->SetXY( $chartWidth / 0.87, $chartYPos + 0.35 );
            $pdf->Cell( 1.18, 0.4, $chartXLabel, 0, 0, 'C' );
            $pdf->SetXY( $chartXPos + 0.27, $chartYPos - $chartHeight - 0.47 );
            $pdf->Cell( 0.79, 0.4, $chartYLabel, 0, 0, 'R' );
            
            // Create the bars
            $xPos = $chartXPos + 1.57;
            $bar = 0;
            
            foreach ( $data as $dataRow ) {

                // Total up the sales figures for this product
                $totalSales = 0;
                foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;

                // Create the bar
                $colourIndex = $bar % count( $chartColours );
                $pdf->SetFillColor( $chartColours[$colourIndex][0], $chartColours[$colourIndex][1], $chartColours[$colourIndex][2] );
                $pdf->Rect( $xPos, $chartYPos - ( $totalSales / $yScale ), $barWidth, $totalSales / $yScale, 'DF' );
                $xPos += ( 1 / $xScale );
                $bar++;
            }
            
           $pdf->AddPage();
            
            // Diagrama de accionistas
            // Begin configuration

            $chartYPos = 5.5;
            $chartYLabel = utf8_decode("Accionistas");
            $max = max($accRegistrados, count($accionistasComparativa));
            $numberOfDigits = strlen((string)$max);
            $precision = 0 - $numberOfDigits + 2;
            $chartYStep = round($max / 8, $precision);

            $data = array(
                array($accRegistrados), array(count($accionistasComparativa))       
            );
            

            // End configuration
            // 
            // 
            // Compute the X scale
            $xScale = count($rowLabels) / ( $chartWidth - 1.6 );

            // Compute the Y scale

            $maxTotal = 0;

            foreach ( $data as $dataRow ) {
              $totalSales = 0;
              foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;
              $maxTotal = ( $totalSales > $maxTotal ) ? $totalSales : $maxTotal;
            }

            $yScale = $maxTotal / $chartHeight;
            
            if($yScale != 0){
            
                // Compute the bar width
                $barWidth = ( 1 / $xScale ) / 1.5;

                // Add the axes:

                $pdf->SetFont( 'Arial', '', 10 );

                // X axis
                $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + $chartWidth, $chartYPos );

                for ( $i=0; $i < count( $rowLabels ); $i++ ) {
                  $pdf->SetXY( $chartXPos + 1.6 +  $i / $xScale, $chartYPos );
                  $pdf->Cell( $barWidth, 0.4, $rowLabels[$i], 0, 0, 'C' );
                }

                // Y axis
                $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + 1.18, $chartYPos - $chartHeight - 0.3 );

                for ( $i=0; $i <= $maxTotal; $i += $chartYStep ) {
                  $pdf->SetXY( $chartXPos + 0.27, $chartYPos - 0.2 - $i / $yScale );
                  $pdf->Cell( 0.79, 0.4, '' . number_format( $i ), 0, 0, 'R' );
                  $pdf->Line( $chartXPos + 1.1, $chartYPos - $i / $yScale, $chartXPos + 1.18, $chartYPos - $i / $yScale );
                }

                // Add the axis labels
                $pdf->SetFont( 'Arial', 'B', 12 );
                $pdf->SetXY( $chartWidth / 0.87, $chartYPos + 0.35 );
                $pdf->Cell( 1.18, 0.4, $chartXLabel, 0, 0, 'C' );
                $pdf->SetXY( $chartXPos + 0.27, $chartYPos - $chartHeight - 0.47 );
                $pdf->Cell( 0.79, 0.4, $chartYLabel, 0, 0, 'R' );

                // Create the bars
                $xPos = $chartXPos + 1.57;
                $bar = 0;

                foreach ( $data as $dataRow ) {

                    // Total up the sales figures for this product
                    $totalSales = 0;
                    foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;

                    // Create the bar
                    $colourIndex = $bar % count( $chartColours );
                    $pdf->SetFillColor( $chartColours[$colourIndex][0], $chartColours[$colourIndex][1], $chartColours[$colourIndex][2] );
                    $pdf->Rect( $xPos, $chartYPos - ( $totalSales / $yScale ), $barWidth, $totalSales / $yScale, 'DF' );
                    $xPos += ( 1 / $xScale );
                    $bar++;
                }
            
            }
            
            // Diagrama de acciones
            // Begin configuration

            $chartYPos = 9.5;
            $chartYLabel = utf8_decode("Acciones");
            $max = max($accionesTotales, $accionesTotalesComparativa);
            $numberOfDigits = strlen((string)$max);
            $precision = 0 - $numberOfDigits + 2;
            $chartYStep = round($max / 8, $precision);

            $data = array(
                array($accionesTotales), array($accionesTotalesComparativa)       
            );
            
            // End configuration
            // 
            // 
            // Compute the X scale
            $xScale = count($rowLabels) / ( $chartWidth - 1.6 );

            // Compute the Y scale

            $maxTotal = 0;

            foreach ( $data as $dataRow ) {
              $totalSales = 0;
              foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;
              $maxTotal = ( $totalSales > $maxTotal ) ? $totalSales : $maxTotal;
            }

            $yScale = $maxTotal / $chartHeight;
            
            if($yScale != 0){
            
                // Compute the bar width
                $barWidth = ( 1 / $xScale ) / 1.5;

                // Add the axes:

                $pdf->SetFont( 'Arial', '', 10 );

                // X axis
                $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + $chartWidth, $chartYPos );

                for ( $i=0; $i < count( $rowLabels ); $i++ ) {
                  $pdf->SetXY( $chartXPos + 1.6 +  $i / $xScale, $chartYPos );
                  $pdf->Cell( $barWidth, 0.4, $rowLabels[$i], 0, 0, 'C' );
                }

                // Y axis
                $pdf->Line( $chartXPos + 1.18, $chartYPos, $chartXPos + 1.18, $chartYPos - $chartHeight - 0.3 );

                for ( $i=0; $i <= $maxTotal; $i += $chartYStep ) {
                  $pdf->SetXY( $chartXPos + 0.27, $chartYPos - 0.2 - $i / $yScale );
                  $pdf->Cell( 0.79, 0.4, '' . number_format( $i ), 0, 0, 'R' );
                  $pdf->Line( $chartXPos + 1.1, $chartYPos - $i / $yScale, $chartXPos + 1.18, $chartYPos - $i / $yScale );
                }

                // Add the axis labels
                $pdf->SetFont( 'Arial', 'B', 12 );
                $pdf->SetXY( $chartWidth / 0.87, $chartYPos + 0.35 );
                $pdf->Cell( 1.18, 0.4, $chartXLabel, 0, 0, 'C' );
                $pdf->SetXY( $chartXPos + 0.27, $chartYPos - $chartHeight - 0.47 );
                $pdf->Cell( 0.79, 0.4, $chartYLabel, 0, 0, 'R' );

                // Create the bars
                $xPos = $chartXPos + 1.57;
                $bar = 0;

                foreach ( $data as $dataRow ) {

                    // Total up the sales figures for this product
                    $totalSales = 0;
                    foreach ( $dataRow as $dataCell ) $totalSales += $dataCell;

                    // Create the bar
                    $colourIndex = $bar % count( $chartColours );
                    $pdf->SetFillColor( $chartColours[$colourIndex][0], $chartColours[$colourIndex][1], $chartColours[$colourIndex][2] );
                    $pdf->Rect( $xPos, $chartYPos - ( $totalSales / $yScale ), $barWidth, $totalSales / $yScale, 'DF' );
                    $xPos += ( 1 / $xScale );
                    $bar++;
                }
            }
        }
        
    }
    
    private function getParametrosComparativa($platform_database){
        
        //$em = $this->getContainer()->get('doctrine')->getManager('db_connection');
        //$platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findByDbname($platform_database);
        PlatformsManager::switchDb($platform_database);
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $em->clear();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();
        
        $em->clear();

        return $parameters;
    }
    
    private function getAccionistasComparativa($platform_database){
        //$em = $this->getContainer()->get('doctrine')->getManager('db_connection');
        //$platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findByDbname($platform_database);
        PlatformsManager::switchDb($platform_database);
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $em->clear();

        $accionistas = $em->getRepository('Lugh\WebAppBundle\Entity\Accionista')->findAll();
        
        $em->clear();

        return $accionistas;
    }
}
