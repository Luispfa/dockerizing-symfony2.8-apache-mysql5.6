<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Accion")
 */
class AccionController extends Controller {

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion', 'Personal');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $accions = $storage->getLastAccions();
                $items = array('accions' => $accions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accions
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetVeAction() {
        $valid = $this->checkHeaders();
 
        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion', 'Personal');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $accions = $storage->getLastAccionsVe();
                $items = array('accions' => $accions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }
  
// "get_accions"     [GET] /accions/ve
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAvAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion', 'Personal');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $accions = $storage->getLastAccionsAv();
                $items = array('accions' => $accions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

 // "get_accions"     [GET] /accions/av  

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetMovimientosfileAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $d = date("YmdHis");
            $filename = 'Voto' . $d . '.txt';

            try {
                $response = $this->get('lugh.movimientosFile')->COBSA($filename);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetMovimientosfiletotalAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $d = date("YmdHis");
            $filename = 'Voto' . $d . '.txt';

            try {
                $response = $this->get('lugh.movimientosFile')->Total($filename);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetMovimientosfiledateAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $d = date("YmdHis");
            $filename = 'Voto' . $d . '.txt';
            $request = $this->get('request');

            $day = intval($request->get('day', 0));
            $month = intval($request->get('month', 0));
            $year = intval($request->get('year', 0));

            $date = new \DateTime();
            $date->setDate($year, $month, $day);

            try {
                $response = $this->get('lugh.movimientosFile')->Fecha($filename, $date);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetExcelAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $accions = $storage->getLastAccions();
            $puntos = $storage->getPuntos();
            $tipoVotos = $storage->getTipoVotos();

            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("Header")
                    ->setLastModifiedBy("Header")
                    ->setTitle("Voto/delegación electrónica")
                    ->setSubject($this->container->get('lugh.parameters')->getByKey('Config.customer.title', 'Lugh vote/proxy'))
                    ->setDescription("")
                    ->setKeywords("")
                    ->setCategory("");

            $col = 0;
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($col++, 1, 'Nombre')
                    ->setCellValueByColumnAndRow($col++, 1, 'Representado por')
                    ->setCellValueByColumnAndRow($col++, 1, 'PF/PJ')
                    ->setCellValueByColumnAndRow($col++, 1, 'Tipo Doc')
                    ->setCellValueByColumnAndRow($col++, 1, 'Documento')
                    ->setCellValueByColumnAndRow($col++, 1, 'Telefono')
                    ->setCellValueByColumnAndRow($col++, 1, 'Acciones')
                    ->setCellValueByColumnAndRow($col++, 1, 'Correo electrónico')
                    ->setCellValueByColumnAndRow($col++, 1, 'Observaciones')
                    ->setCellValueByColumnAndRow($col++, 1, 'Actuación')
                    ->setCellValueByColumnAndRow($col++, 1, 'Delegación')
                    ->setCellValueByColumnAndRow($col++, 1, 'Sustitución')
                    ->setCellValueByColumnAndRow($col++, 1, 'Fecha')
            ;

            $row = 1;
            $maxcol = 0;
            $tipos = [];
            foreach ($accions as $accion) {
                $row++;
                $col = 0;
                $acc = $accion->getAccionista();
                //$del = $method_exists($accion,'getDelegado') ? $accion->getDelegado()->getName() : '';
                $obs = $accion::nameClass == 'Delegacion' ? $accion->getObservaciones() : '';
                $phpExcelObject->getActiveSheet()
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getName())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getRepresentedBy())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getRepresentedBy() == '' ? 'PF' : 'PJ')
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentType())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getDocumentNum())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getTelephone())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getSharesNum())
                        ->setCellValueByColumnAndRow($col++, $row, $acc->getUser()->getEmail())
                        ->setCellValueByColumnAndRow($col++, $row, $obs)
                        ->setCellValueByColumnAndRow($col++, $row, $this->getActuacion($accion))
                        ->setCellValueByColumnAndRow($col++, $row, $accion::nameClass == 'Delegacion' ? ($accion->getDelegado()->getNombre()) : '')
                        ->setCellValueByColumnAndRow($col++, $row, $accion::nameClass == 'Delegacion' ? ($accion->getSustitucion() == 1 ? 'Sí' : 'No') : '')
                        ->setCellValueByColumnAndRow($col++, $row, $accion->getDateTime()->format('d-m-Y H:i:s'))
                ;

                $count = 0;
                foreach ($accion->getVotoAbsAdicional() as $abs_adicional) {
                    $aux_col = $col;
                    $name = 'Abstención adicional (' . $abs_adicional->getAbsAdicional()->getTipoVoto()->getName() . ') - ' . $count;
                    if (array_key_exists($name, $tipos)) {
                        $col = $tipos[$name];
                    } else {
                        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $name);
                        $tipos[$name] = $col;
                    }

                    $phpExcelObject->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, $accion::nameClass != 'Anulacion' ? $abs_adicional->getOpcionVoto()->getNombre() : '');

                    $col = $aux_col;
                    $col++;
                    $count++;
                }

                if ($col > $maxcol) {
                    $maxcol = $col;
                }
            }

            $puntosBckp = $puntos;
            foreach ($tipoVotos as $tipo) {
                $col = $maxcol;
                $puntos = $puntosBckp;
                $tp = sizeof($tipoVotos) > 1 ? $tipo->getTipo() . ' - ' : '';

                $col = $this->printPuntos($puntos, $phpExcelObject, $col, $tipo, $tp, $accions);
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

            $response->headers->set('Content-Disposition', 'attachment;filename=stream-file.xls');

            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');

            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accions/excel
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetExcelAvAction() {
        
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //$filename = 'Acceso' . date("YmdHis") . '.txt';
            $subject = $this->container->get('lugh.parameters')->getByKey('Config.customer.title', 'Lugh sharesholders');
            try {
                $response = $this->get('lugh.avFile')->votoExcel($subject);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accions/excel/av
    
     /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetTotalAvAction() {
        $valid = $this->checkHeaders();
 
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //$d = date("YmdHis");
            //$filename = 'Voto' . $d . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->votoTotal($acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accions/total/av
    
     /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetLastAvAction() {
        $valid = $this->checkHeaders();
 
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $filename = 'VotacionTelematica' . date("YmdHis") . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->votoLast($filename, $acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accions/last/av

    public function getAction($id) {// GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $accion = $storage->getAccion($id);
                $items = array('accions' => $accion);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /accions/{id}

    public function postAction() {// Create Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "new_opcionesaccions"     [POST] /accions

    public function putAction($id) {// Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "put_resource"      [PUT] /accions/{id}

    public function deleteAction($id) {// DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_resource"      [DELETE] /resource/{id}

    public function getCommentsAction($slug, $id) {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "get_resource_comments"     [GET] /™accions/{slug}/comments/{id}

    private function getActuacion($action) {
        if ($action::nameClass == 'Delegacion') {
            $intention = count($action->getVotacion()) > 0;
            if ($action->getDelegado()->getIsDirector()) {
                return $intention ? 'Delegación en el Presidente con intención de voto' :
                        "Delegación en el Presidente sin intención de voto";
            } else if ($action->getDelegado()->getIsConseller()) {
                return $intention ? 'Delegación en Miembro del Consejo con intención de voto' :
                        "Delegación en Miembro del Consejo sin intención de voto";
            } else {
                return $intention ? 'Delegación en Persona con intención de voto' :
                        "Delegación en Persona sin intención de voto";
            }
        } else if ($action::nameClass == 'Voto') {
            return 'Votación';
        } else if ($action::nameClass == 'Anulacion') {
            return 'Anulación';
        } else if ($action::nameClass == 'Av') {
            return 'Asistencia Virtual';
        } else if ($action::nameClass == 'AnulacionAv') {
            return 'Anulación';
        }
        return 'Actuación no definida';
    }

    private function printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions) {
        if (count($lista) == 0) {
            return $col;
        }
        $punto = array_shift($lista);
        if ($tipo->getTipo() != $punto->getTipoVoto()->getTipo()) {
            return $this->printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions);
        }

        $phpExcelObject->getActiveSheet()
                ->setCellValueByColumnAndRow($col, 1, $tp . $punto->getNumPunto());

        $row = 2;
        foreach ($accions as $accion) {
            $votacion = $this->getVotacion($accion, $punto);
            $nombre = $votacion != false ? $votacion->getOpcionVoto()->getNombre() : '';
            $phpExcelObject->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row++, $nombre);
        }

        $col++;
        if (count($subpuntos = $punto->getSubpuntos()->toArray()) > 0) {
            $col = $this->printPuntos($subpuntos, $phpExcelObject, $col, $tipo, $tp, $accions);
        }

        return $this->printPuntos($lista, $phpExcelObject, $col, $tipo, $tp, $accions);
    }

    private function getVotacion($accion, $punto) {
        foreach ($accion->getVotacion() as $votacion) {
            if ($punto == $votacion->getPunto()) {
                return $votacion;
            }
        }
        return false;
    }

    public function checkHeaders() {

        $request = $this->get('request');

        $host = $request->headers->get('host');
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        $valid = true;

        if ($origin != null || $referer != null) {

            if ($origin != null && !strpos($origin, $host)) {
                $valid = false;
            }
            if ($referer != null && !strpos($referer, $host)) {
                $valid = false;
            }
        } else {

            $valid = false;
        }

        return $valid && $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

}
