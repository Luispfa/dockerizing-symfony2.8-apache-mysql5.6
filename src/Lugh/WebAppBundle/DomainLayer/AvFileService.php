<?php

namespace Lugh\WebAppBundle\DomainLayer;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Lugh\WebAppBundle\Lib\External\StoreManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class AvFileService extends LughService{

    public function accesoExcel($subject){
    
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        
        $accesos = $storage->getLastAccesosAv();

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Header")
                ->setLastModifiedBy("Header")
                ->setTitle("Accionistas")
                ->setSubject($subject)
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");

        $col = 0;
        $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValueByColumnAndRow($col++, 1, 'Nombre')
                ->setCellValueByColumnAndRow($col++, 1, 'Documento')
                ->setCellValueByColumnAndRow($col++, 1, 'Acciones')
                ->setCellValueByColumnAndRow($col++, 1, 'Último acceso')
        ;

        $row = 1;
        $maxcol = 0;

        foreach ($accesos as $acceso) {
            $row++;
            $col = 0;
            $acc = $acceso->getAccionista();

            $phpExcelObject->getActiveSheet()
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getName())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentNum())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getSharesNum())
                    ->setCellValueByColumnAndRow($col++, $row, $acceso->getDateTime()->format('d-m-Y H:i:s'))
            ;

            $count = 0;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=AsistenciaTelematica.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }
    
    public function accesoTotal($accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $accesos = $storage->getLastAccesosAv();
        
        foreach ($accesos as $acceso) {
            //$accionista = $acceso->getAccionista();
            $content .= AvFileService::RegistroDatos($acceso, $accionesReferencia, 'acceso', '0');
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', 'AsistenciaTelematica.txt'));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    public function accesoLast($filename, $accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $accesos = $storage->getLastAccesosAv();
        
        foreach ($accesos as $acceso) {
            $accionista = $acceso->getAccionista();
            
            if (AvFileService::AccesoExportable($accionista, 'av')) {
                $content .= AvFileService::RegistroDatos($acceso, $accionesReferencia, 'acceso', '0');
                $acceso->setMovFileTagged($filename);
                $storage->save($acceso);
            }
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    public function abandonoExcel($subject){
        
        $storage = $this->get('lugh.server')->getStorage();
        $response = new Response();
        $desertions = $storage->getDesertions();

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Header")
                ->setLastModifiedBy("Header")
                ->setTitle("Abandonos")
                ->setSubject($subject)
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");

        $col = 0;
        $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValueByColumnAndRow($col++, 1, 'Nombre')
                ->setCellValueByColumnAndRow($col++, 1, 'Documento')
                ->setCellValueByColumnAndRow($col++, 1, 'Acciones')
                ->setCellValueByColumnAndRow($col++, 1, 'Fecha salida')
        ;

        $row = 1;
        $maxcol = 0;

        foreach ($desertions as $desertion) {
            $row++;
            $col = 0;
            $acc = $desertion->getAutor();

            $phpExcelObject->getActiveSheet()
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getName())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentNum())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getSharesNum())
                    ->setCellValueByColumnAndRow($col++, $row, $desertion->getDateTime()->format('d-m-Y H:i:s'))
            ;

            $count = 0;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');

        $response->headers->set('Content-Disposition', 'attachment;filename=AbandonoTelematico.xls');

        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
        
    }
    
    public function abandonoTotal($accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $desertions = $storage->getDesertions();
        
        foreach ($desertions as $desertion) {
            //$accionista = $acceso->getAccionista();
            $content .= AvFileService::RegistroDatos($desertion, $accionesReferencia, 'abandono', '3');
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', 'AbandonoTelematico.txt'));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    public function abandonoLast($filename, $accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $desertions = $storage->getDesertions();
        
        foreach ($desertions as $desertion) {
            //$accionista = $desertion->getAccionista();
            
            if ($desertion->getMovFileTagged() == null) {
                $content .= AvFileService::RegistroDatos($desertion, $accionesReferencia, 'abandono', '3');
                $desertion->setMovFileTagged($filename);
                $storage->save($desertion);
            }
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    public function votoExcel($subject){
        
        $storage = $this->get('lugh.server')->getStorage();
        $response = new Response();
        $accions = $storage->getLastAccionsAv();
        $puntos = $storage->getPuntos();
        $tipoVotos = $storage->getTipoVotos();


        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Header")
                ->setLastModifiedBy("Header")
                ->setTitle("Votos")
                ->setSubject($subject)
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");

        $col = 0;
        $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($col++, 1, 'Nombre')
                    ->setCellValueByColumnAndRow($col++, 1, 'Tipo Doc')
                    ->setCellValueByColumnAndRow($col++, 1, 'Documento')
                    ->setCellValueByColumnAndRow($col++, 1, 'Telefono')
                    ->setCellValueByColumnAndRow($col++, 1, 'Acciones')
                    ->setCellValueByColumnAndRow($col++, 1, 'Correo electrónico')
                    ->setCellValueByColumnAndRow($col++, 1, 'Actuación')
                    ->setCellValueByColumnAndRow($col++, 1, 'Fecha')
            ;

        $row = 1;
        $maxcol = 0;

       foreach ($accions as $accion) {
            $row++;
            $col = 0;
            $acc = $accion->getAccionista();
            $phpExcelObject->getActiveSheet()
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getName())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentType())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentNum())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getTelephone())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getSharesNum())
                    ->setCellValueByColumnAndRow($col++, $row, $acc->getUser()->getEmail())
                    ->setCellValueByColumnAndRow($col++, $row, AvFileService::getActuacion($accion))
                    ->setCellValueByColumnAndRow($col++, $row, $accion->getDateTime()->format('d-m-Y H:i:s'))
            ;

            $count = 0;

            if ($col > $maxcol) {
                $maxcol = $col;
            }
        }
        
        $puntosBckp = $puntos;
        foreach ($tipoVotos as $tipo) {
            $col = $maxcol;
            $puntos = $puntosBckp;
            $tp = sizeof($tipoVotos) > 1 ? $tipo->getTipo() . ' - ' : '';

            $col = AvFileService::printPuntos($puntos, $phpExcelObject, $col, $tipo, $tp, $accions);
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');

        $response->headers->set('Content-Disposition', 'attachment;filename=VotacionTelematica.xls');

        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
        
    }
    
    public function votoTotal($accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $actions = $storage->getLastAccionsAv();
        $puntos  = $storage->getCobsaPuntos();
        
        foreach ($actions as $action) {
            
            if($action::nameClass == 'AnulacionAv'){
                continue;
            }
            
            $content .= AvFileService::RegistroDatos($action, $accionesReferencia, 'voto', '1', $puntos);
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', 'VotacionTelematica.txt'));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    public function votoLast($filename, $accionesReferencia){
     
        $storage = $this->get('lugh.server')->getStorage();
	$response = new Response();
        $content = '';
        
        $actions = $storage->getLastAccionsAv();
        $puntos  = $storage->getCobsaPuntos();
        
        foreach ($actions as $action) {
            
            if($action::nameClass == 'AnulacionAv'){
                continue;
            }
            
            if ($action->getMovFileTagged() == null) {
                $content .= AvFileService::RegistroDatos($action, $accionesReferencia, 'voto', '1', $puntos);
                $action->setMovFileTagged($filename);
                $storage->save($action);
            }
        }
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
	$response->headers->set('Content-Disposition', $disposition);
	$response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);
                
        return $response;
    }
    
    
    
    static private function AccesoExportable($accionista, $discr)
    {
        $accesosAccionista = $accionista->getAllAccesoForFind($discr);
        
        $accesos =  array_filter($accesosAccionista, function($element){
            //var_dump($element::nameClass);
            return $element->getMovFileTagged() == null;
        });
        
        return count($accesos) == count($accesosAccionista);
    }
    
    static private function RegistroDatos($action, $accionesReferencia, $tipo, $tipo_documento, $puntos = null){
        
        $accionista = $tipo == 'acceso' || $tipo == 'voto' ? $action->getAccionista() : $action->getAutor();
        $canal = $tipo == 'voto' ? 'VE' : 'AE';
        
        $tipo_persona = $accionista->getDocumentType() == "cif" ? "J" : "F";
        $fecha   = $action->getDateTime()->format('Ymd');
        $hora    = $action->getDateTime()->format('H:i:s');
        
        $valid_json = $accionista->getValidJson();
        $content = '';
        
        if ((!(!isset($valid_json) || trim($valid_json) === '')) && $accionesReferencia)
        {
            $referencias = json_decode($accionista->getValidJson(), true);
            foreach($referencias as $referencia){

                $rd = array();

                $rd[]=AvFileService::csprintf("%02.2s",      '01');                                  //TipoRegistro
                $rd[]=AvFileService::csprintf("%-36.36s",    $referencia['Referencia']);             //CodigodeBarras
                $rd[]=AvFileService::csprintf("%015.15s",    $referencia['Acciones']);               //Numero Títulos
                $rd[]=AvFileService::csprintf("%01.1s",      $tipo_documento);                       //Tipo Documento
                $rd[]=AvFileService::csprintf("%-18.18s",    $accionista->getDocumentNum());         //NIF
                $rd[]=AvFileService::csprintf("%-100.100s",  $accionista->getName());                //Persona
                $rd[]=AvFileService::csprintf("%01.1s",      $tipo_persona);                         //Tipo de Persona
                $rd[]=AvFileService::csprintf("%08.8s",      $fecha);                                //Fecha
                $rd[]=AvFileService::csprintf("%08.8s",      $hora);                                 //Hora
                $rd[]=AvFileService::csprintf("%02.2s",      $canal);                                //Canal

                $content .= implode($rd).chr(13).chr(10);
                
                if ($tipo == 'voto')
                {
                    if(count($votacion = $action->getVotacion())>0){
                        $cont = 1;
                        foreach($puntos as $punto){
                            if (count($punto->getSubpuntos()) == 0) {
                                if($voto = AvFileService::getVotacion($action,$punto)){
                                    $content .= AvFileService::RegistroDetalleVotaciones($punto,$voto,$cont,$referencia['Referencia'],$referencia['Acciones']).chr(13).chr(10);
                                }
                                $cont++;
                            }
                        }
                    }
                    
                }
            }
        }
        else
        {
            $rd = array();

            $rd[]=AvFileService::csprintf("%02.2s",      '01');                                  //TipoRegistro
            $rd[]=AvFileService::csprintf("%-36.36s",    '');                                   //CodigodeBarras
            $rd[]=AvFileService::csprintf("%015.15s",    $accionista->getSharesNum());           //Numero Títulos
            $rd[]=AvFileService::csprintf("%01.1s",      $tipo_documento);                       //Tipo Documento
            $rd[]=AvFileService::csprintf("%-18.18s",    $accionista->getDocumentNum());         //NIF
            $rd[]=AvFileService::csprintf("%-100.100s",  $accionista->getName());                //Persona
            $rd[]=AvFileService::csprintf("%01.1s",      $tipo_persona);                         //Tipo de Persona
            $rd[]=AvFileService::csprintf("%08.8s",      $fecha);                                //Fecha
            $rd[]=AvFileService::csprintf("%08.8s",      $hora);                                 //Hora
            $rd[]=AvFileService::csprintf("%02.2s",      $canal);                                //Canal
            
            $content = implode($rd).chr(13).chr(10);
            
            if ($tipo == 'voto')
            {
                if(count($votacion = $action->getVotacion())>0){
                    $cont = 1;
                    foreach($puntos as $punto){
                        if (count($punto->getSubpuntos()) == 0) {
                            if($voto = AvFileService::getVotacion($action,$punto)){
                                $content .= AvFileService::RegistroDetalleVotaciones($punto,$voto,$cont,'',$accionista->getSharesNum()).chr(13).chr(10);
                            }
                            $cont++;
                        }
                    }
                }

            }
            
        }

        return $content;
        
    }
    
    static private function csprintf ($format) 
    {
      $args = func_get_args();

      for ($i = 1; $i < count($args); $i++) 
      {
            $args [$i] = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $args [$i]);
      }

      return call_user_func_array('sprintf', $args);
    }
    
    static private function getActuacion($action) {
        if ($action::nameClass == 'Av') {
            return 'Asistencia Virtual';
        } else if ($action::nameClass == 'AnulacionAv') {
            return 'Anulación';
        }
        return 'Actuación no definida';
    }
    
    static private function printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions) {
        if (count($lista) == 0) {
            return $col;
        }
        $punto = array_shift($lista);
        if ($tipo->getTipo() != $punto->getTipoVoto()->getTipo()) {
            return AvFileService::printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions);
        }

        $phpExcelObject->getActiveSheet()
                ->setCellValueByColumnAndRow($col, 1, $tp . $punto->getNumPunto());

        $row = 2;
        foreach ($accions as $accion) {
            $votacion = AvFileService::getVotacion($accion, $punto);
            $nombre = $votacion != false ? $votacion->getOpcionVoto()->getNombre() : '';
            $phpExcelObject->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row++, $nombre);
        }

        $col++;
        if (count($subpuntos = $punto->getSubpuntos()->toArray()) > 0) {
            $col = AvFileService::printPuntos($subpuntos, $phpExcelObject, $col, $tipo, $tp, $accions);
        }

        return AvFileService::printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions);
    }
    
    static private function getVotacion($accion, $punto) {
        foreach ($accion->getVotacion() as $votacion) {
            if ($punto == $votacion->getPunto()) {
                return $votacion;
            }
        }
        return false;
    }
    
    static private function RegistroDetalleVotaciones($punto,$voto,$cont,$referencia, $num_shares){
        $id_file = $punto->getIdFile();
        $id      = $punto->getExtra() > 0 ? '00' : $id_file == null ? $cont : $id_file;
        $sentido = $voto->getOpcionVoto()->getSymbol();

        $rd = array();
        $rd[]=AvFileService::csprintf("%02.2s",    '02');                         //TipoRegistro
        $rd[]=AvFileService::csprintf("%-36.36s",  $referencia);                  //CodigodeBarras
        $rd[]=AvFileService::csprintf("%02.2s",    $id);                          //Punto
        $rd[]=AvFileService::csprintf("%015.15s",  doubleval($num_shares));       //Numero Titulos
        $rd[]=AvFileService::csprintf("%01.1s",    $sentido);                     //Sentido Voto

        return implode($rd);
    }
}


