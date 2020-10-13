<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Acceso")
 */
class AccesoController extends Controller {

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Acceso', 'Personal');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $accions = $storage->getLastAccesos();
                $items = array('accesos' => $accions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accions"     [GET] /accesos
    
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAvAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Acceso', 'Personal');
            try {
                $accions = $storage->getLastAccesosAv();
                $items = array('accesos' => $accions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

 // "get_accions"     [GET] /accesos/av  

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
                $response = $this->get('lugh.avFile')->accesoExcel($subject);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accesos"     [GET] /accesos/excel/av
    
        /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetTotalAvAction() {
        
        $valid = $this->checkHeaders();
        
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //$filename = 'Acceso' . date("YmdHis") . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->accesoTotal($acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accesos"     [GET] /accesos/total/av
    
        /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetLastAvAction() {
        
        $valid = $this->checkHeaders();
        
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $filename = 'AsistenciaTelematica' . date("YmdHis") . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->accesoLast($filename, $acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accesos"     [GET] /accesos/last/av
       
    public function getAction($id) {// GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Acceso');
            try {
                $accion = $storage->getAcceso($id);
                $items = array('accesos' => $accion);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /accesos/{id}

    public function postAction() {// Create Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "new_opcionesaccions"     [POST] /accesos

    public function putAction($id) {// Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "put_resource"      [PUT] /accesos/{id}

    public function deleteAction($id) {// DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_resource"      [DELETE] /accesos/{id}

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
