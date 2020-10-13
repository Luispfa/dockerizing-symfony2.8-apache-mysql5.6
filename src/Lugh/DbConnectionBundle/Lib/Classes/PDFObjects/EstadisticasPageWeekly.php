<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDFObjects;

use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Site;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Page;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\PageContainer;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\zebraTable;

class EstadisticasPageWeekly extends PageContainer implements Page{
    
    var $apps;
    function __construct($apps, $startDate = null, $endDate = null){
        $this->apps         = $apps;
        
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
        
    }

}
