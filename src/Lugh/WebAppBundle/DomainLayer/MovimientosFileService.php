<?php

namespace Lugh\WebAppBundle\DomainLayer;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Lugh\WebAppBundle\Lib\External\StoreManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class MovimientosFileService extends LughService{
	
	public function COBSA($filename){
		$storage = $this->get('lugh.server')->getStorage();
		$response = new Response();

		$content = '';
		$content = MovimientosFileService::setHeader($content);
		$puntos  = $storage->getPuntos();
		$actions = $storage->getAccionsNoFile();

		$registros1=0;
		$registros2=0;
		$totalAcciones=0;
		foreach($actions as $action){
			$registros1++;
			$accionista = $action->getAccionista();
			$totalAcciones += $accionista->getSharesNum();

			$content .= chr(13).chr(10).MovimientosFileService::RegistroDatos($action);
			if(count($votacion = $action->getVotacion())>0){
				$cont = 1;
				foreach($puntos as $punto){
					$registros2++;
					if($voto = MovimientosFileService::getVotacion($action,$punto)){
						$content .= chr(13).chr(10).MovimientosFileService::RegistroDetalleVotaciones($punto,$voto,$cont,$accionista);
					} else {
						if(count($punto->getSubpuntos())==0){
							//Si es informativo, ahora queda en blanco
							$content .= chr(13).chr(10).MovimientosFileService::RegistroDetallePuntoEnBlanco($punto,$cont,$accionista);
						}
					}
					$cont++;
				}
			}
			$action->setMovFileTagged($filename);
			$storage->save($action);
		}

		$content .= chr(13).chr(10).MovimientosFileService::RegistroFinal($registros1,$registros2,$totalAcciones);
		
		try {
			StoreManager::StoreMovimientosFile($filename, $content);
        } catch (Exception $exc) {
            return $exc->getMessage();	
        }
		

		$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', 'text/plain');
		$response->setContent($content);

		return $response;
	}

	public function Total($filename){
		$storage = $this->get('lugh.server')->getStorage();
		$response = new Response();

		$content = '';
		$content = MovimientosFileService::setHeader($content);
		$puntos  = $storage->getPuntos();
		$actions = $storage->getAccions();

		$registros1=0;
		$registros2=0;
		$totalAcciones=0;
		foreach($actions as $action){
			$registros1++;
			$accionista = $action->getAccionista();
			$totalAcciones += $accionista->getSharesNum();

			$content .= chr(13).chr(10).MovimientosFileService::RegistroDatos($action);
			if(count($votacion = $action->getVotacion())>0){
				$cont = 1;
				foreach($puntos as $punto){
					$registros2++;
					if($voto = MovimientosFileService::getVotacion($action,$punto)){
						$content .= chr(13).chr(10).MovimientosFileService::RegistroDetalleVotaciones($punto,$voto,$cont,$accionista);
					} else {
						if(count($punto->getSubpuntos())==0){
							//Si es informativo, ahora queda en blanco
							$content .= chr(13).chr(10).MovimientosFileService::RegistroDetallePuntoEnBlanco($punto,$cont,$accionista);
						}
					}
					$cont++;
				}
			}
		}

		$content .= chr(13).chr(10).MovimientosFileService::RegistroFinal($registros1,$registros2,$totalAcciones);
		
		$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', 'text/plain');
		$response->setContent($content);

		return $response;
	}

	public function Fecha($filename,$fecha){
		$storage = $this->get('lugh.server')->getStorage();
		$response = new Response();

		$content = '';
		$content = MovimientosFileService::setHeader($content);
		$puntos  = $storage->getPuntos();
		$actions = $storage->getAccions();

		$registros1=0;
		$registros2=0;
		$totalAcciones=0;
		foreach($actions as $action){
			$actDateData = $action->getDateTimeCreate();

			if($fecha->format('Y') != $actDateData->format('Y') ||
			   $fecha->format('m') != $actDateData->format('m') ||
			   $fecha->format('d') != $actDateData->format('d')){
				continue;
			}

			$registros1++;
			$accionista = $action->getAccionista();
			$totalAcciones += $accionista->getSharesNum();

			$content .= chr(13).chr(10).MovimientosFileService::RegistroDatos($action);
			if(count($votacion = $action->getVotacion())>0){
				$cont = 1;
				foreach($puntos as $punto){
					$registros2++;
					if($voto = MovimientosFileService::getVotacion($action,$punto)){
						$content .= chr(13).chr(10).MovimientosFileService::RegistroDetalleVotaciones($punto,$voto,$cont,$accionista);
					} else {
						if(count($punto->getSubpuntos())==0){
							//Si es informativo, ahora queda en blanco
							$content .= chr(13).chr(10).MovimientosFileService::RegistroDetallePuntoEnBlanco($punto,$cont,$accionista);
						}
					}
					$cont++;
				}
			}
		}

		$content .= chr(13).chr(10).MovimientosFileService::RegistroFinal($registros1,$registros2,$totalAcciones);
		
		$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', 'text/plain');
		$response->setContent($content);

		return $response;
	}

	static private function RegistroFinal($r1,$r2,$titulos)
	{
		$rf = array();
		$rf[]=MovimientosFileService::csprintf("%02.2s", '99');       //TipoRegistro
		$rf[]=MovimientosFileService::csprintf("%015.15s",$r1);       //Total Registros 01
		$rf[]=MovimientosFileService::csprintf("%015.15s",$r2);       //Total Registros 02
		$rf[]=MovimientosFileService::csprintf("%015.15s",$r1+$r2);   //Total Registros 
		$rf[]=MovimientosFileService::csprintf("%015.15s",$titulos);  //Total Titulos
		$rf[]=MovimientosFileService::csprintf("%-293.293s",'');      //Filler
		return implode($rf);
	}

	static private function RegistroDetallePuntoEnBlanco($punto,$cont,$accionista)
	{
		$id_file = $punto->getIdFile();
		$id      = $punto->getExtra() > 0 ? '00' : $id_file == null ? $cont : $id_file;
		$num_shares = $accionista->getSharesNum();

		$rd = array();
		$rd[]=MovimientosFileService::csprintf("%02.2s", '02');            //TipoRegistro
		$rd[]=MovimientosFileService::csprintf("%036.36s",'0');            //CodigodeBarras
		$rd[]=MovimientosFileService::csprintf("%02.2s", $id); 			 //Punto
		$rd[]=MovimientosFileService::csprintf("%015.15s",doubleval($num_shares));  //Numero Titulos
		$rd[]=MovimientosFileService::csprintf("%01.1s", 'B');             //Sentido Voto
		$rd[]=MovimientosFileService::csprintf("%-299.299s",'');      //Filler
		return implode($rd);
	}

	static private function RegistroDetalleVotaciones($punto,$voto,$cont,$accionista){
		$id_file = $punto->getIdFile();
		$id      = $punto->getExtra() > 0 ? '00' : $id_file == null ? $cont : $id_file;
		$sentido = $voto->getOpcionVoto()->getSymbol();
		$num_shares = $accionista->getSharesNum();

		$rd = array();
		$rd[]=MovimientosFileService::csprintf("%02.2s", '02');                 //TipoRegistro
		$rd[]=MovimientosFileService::csprintf("%036.36s",'0');                 //CodigodeBarras
		$rd[]=MovimientosFileService::csprintf("%02.2s", $id);                  //Punto
		$rd[]=MovimientosFileService::csprintf("%015.15s", doubleval($num_shares));   //Numero Titulos
		$rd[]=MovimientosFileService::csprintf("%01.1s", $sentido);             //Sentido Voto
		$rd[]=MovimientosFileService::csprintf("%-299.299s",'');                //Filler

		return implode($rd);
	}

	static private function getVotacion($accion, $punto) {
                foreach ($accion->getVotacion() as $votacion) {
			if ($punto == $votacion->getPunto()) {
				return $votacion;
			}
		}
		return false;
	}
        
	private function printPuntos($lista, $count, $tipo, $accions) {
		if (count($lista) == 0) {
			return $count;
		}
		$punto = array_shift($lista);
		if ($tipo->getTipo() != $punto->getTipoVoto()->getTipo()) {
			return $this->printPuntos($lista, $count, $tipo, $accions);
		}

		$row = 2;
		foreach ($accions as $accion) {
			$votacion = $this->getVotacion($accion, $punto);
			$nombre   = $votacion != false ? $votacion->getOpcionVoto()->getNombre() : '';
		}

		$count++;
		if (count($subpuntos = $punto->getSubpuntos()->toArray()) > 0) {
			$count = $this->printPuntos($subpuntos, $count, $tipo, $accions);
		}

		return $this->printPuntos($lista, $count, $tipo, $accions);
	}

	static private function RegistroDatos($action){
		$accionista = $action->getAccionista();
		$tipo_documento = "2"; //0 delegación, 1 voto
		if($action::nameClass == 'Voto' || $action::nameClass == 'Av'){
			$tipo_documento = "1";
		} else if($action::nameClass == 'Delegacion'){
			$tipo_documento = "0";
		}

		$num_shares = $accionista->getSharesNum();

		if($action::nameClass == 'Anulacion' || $action::nameClass == 'AnulacionAv'){
			$actionAnulada = $action->getAccionAnterior();
			if($actionAnulada::nameClass == 'Voto' || $actionAnulada::nameClass == 'Av'){
				$tipo_documento = "1";
			} else if($actionAnulada::nameClass == 'Delegacion'){
				$tipo_documento = "0";
			}
			$num_shares = 0;
		}

		$tipo_persona = $accionista->getDocumentType() == "cif" ? "J" : "F";
		$persona = $tipo_persona == "F" ? $accionista->getName() : $accionista->getRepresentedBy();
		$fecha   = $action->getDateTime()->format('Ymd');
		$hora    = $action->getDateTime()->format('H:i:s');
		//die(var_dump($action->getDateTime()->format('H:i:s')));
		$actuacion='';

		$delegado_name= '';
		$delegado_num = '';
		if($action::nameClass == 'Delegacion'){
			$delegado = $action->getDelegado();
			if ($delegado->getIsDirector()) {
				$actuacion = 'presidente';
			} else if ($delegado->getIsConseller()) {
				$actuacion = 'consejo';
			} else {
				$actuacion = 'persona';
			}

			$delegado_name = $delegado->getNombre();
			$delegado_num  = $delegado->getDocumentNum();
		}


		$rd = array();

		$rd[]=MovimientosFileService::csprintf("%02.2s",      '01');                                  //TipoRegistro
		$rd[]=MovimientosFileService::csprintf("%036.36s",    '0');                                   //CodigodeBarras
		$rd[]=MovimientosFileService::csprintf("%015.15s",    $num_shares);                           //Numero Títulos
		$rd[]=MovimientosFileService::csprintf("%01.1s",      $tipo_documento);                       //Tipo Documento
		$rd[]=MovimientosFileService::csprintf("%-10.10s",    $actuacion);                            //Delegación
		$rd[]=MovimientosFileService::csprintf("%-18.18s",    $accionista->getDocumentNum());         //NIF
		$rd[]=MovimientosFileService::csprintf("%-100.100s",  $delegado_name);                        //Representante
		$rd[]=MovimientosFileService::csprintf("%018.18s",    $delegado_num);                         //Nif Representante
		$rd[]=MovimientosFileService::csprintf("%08.8s",      $fecha);                                //Fecha
		$rd[]=MovimientosFileService::csprintf("%08.8s",      $hora);                                 //Hora
		$rd[]=MovimientosFileService::csprintf("%02.2s",      'VE');                                  //Canal
		$rd[]=MovimientosFileService::csprintf("%036.36s",    '0');                                   //CodigodeBrrasPadre
		$rd[]=MovimientosFileService::csprintf("%01.1s",      $tipo_persona);                         //Tipo de Persona
		$rd[]=MovimientosFileService::csprintf("%-100.100s",  $accionista->getName());                //Persona

		return implode($rd);
	}

	static private function setHeader($content){
		$h = array();
		$d = date("Ymd");
		$t = 'T';

		$h[]=MovimientosFileService::csprintf("%02.2s",'00');   //TipoRegistro
		$h[]=MovimientosFileService::csprintf("%01.1s",$t);     //Tipo
		$h[]=MovimientosFileService::csprintf("%08.8s",$d);     //Fecha
		$h[]=MovimientosFileService::csprintf("%-344.344s",''); //Filler
		return implode($h);
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
        
        public function PlainAv($filename){
		$storage = $this->get('lugh.server')->getStorage();
		$response = new Response();

		$content = '';
		$content = MovimientosFileService::setHeader($content);
		$puntos  = $storage->getCobsaPuntos();
		$actions = $storage->getLastAccionsAv();

		$registros1=0;
		$registros2=0;
		$totalAcciones=0;
		foreach($actions as $action){
                        
                        if($action::nameClass == 'AnulacionAv'){
                            continue;
                        }
                        
			$registros1++;
			$accionista = $action->getAccionista();
			$totalAcciones += $accionista->getSharesNum();

			$content .= chr(13).chr(10).MovimientosFileService::RegistroDatos($action);
                        if(count($votacion = $action->getVotacion())>0){
				$cont = 1;
                                foreach($puntos as $punto){
                                    if (count($punto->getSubpuntos()) == 0) {
					$registros2++;
					if($voto = MovimientosFileService::getVotacion($action,$punto)){
                                                $content .= chr(13).chr(10).MovimientosFileService::RegistroDetalleVotaciones($punto,$voto,$cont,$accionista);
					}
      					$cont++;
                                    }
                                }
				
			}
		}

		$content .= chr(13).chr(10).MovimientosFileService::RegistroFinal($registros1,$registros2,$totalAcciones);
		
		$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
		$response->headers->set('Content-Disposition', $disposition);+		$response->headers->set('Content-Type', 'text/plain');
		$response->setContent($content);
		return $response;
	}

}
?>
