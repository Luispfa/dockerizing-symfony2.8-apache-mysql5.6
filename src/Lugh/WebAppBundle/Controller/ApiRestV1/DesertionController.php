<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;

/**
 * @RouteResource("Desertion")
 */
class DesertionController extends Controller {

    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $desertions = $storage->getDesertions();
                $items = array('desertions' => $desertions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Personal'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resources"     [GET] /desertions
    
     /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetExcelAction() {
        
        $valid = $this->checkHeaders();
        
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //$filename = 'Acceso' . date("YmdHis") . '.txt';
            $subject = $this->container->get('lugh.parameters')->getByKey('Config.customer.title', 'Lugh sharesholders');
            try {
                $response = $this->get('lugh.avFile')->abandonoExcel($subject);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_desertions"     [GET] /desertions/excel
 
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetTotalAction() {
        
        $valid = $this->checkHeaders();
        
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //$filename = 'Acceso' . date("YmdHis") . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->abandonoTotal($acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_desertions"     [GET] /desertions/total
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetLastAction() {
        
        $valid = $this->checkHeaders();
        
        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $filename = 'AbandonoTelematico' . date("YmdHis") . '.txt';
            $acciones = $this->container->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            $check = $this->container->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);

            try {
                $response = $this->get('lugh.avFile')->abandonoLast($filename, $acciones && $check);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_desertions"     [GET] /desertions/total
    

    public function getAction($id) { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $desertion = $storage->getDesertion($id);
                $items = array('desertions' => $desertion);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Personal'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /desertions/{id}

    public function postAction() { // Create Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $user = $this->getUser();
            $request = $this->get("request");

            try {
                $accionista = $user->getAccionista();
                                   
                $item = $builder->buildDesertion();
                $item->setDateTime(new \DateTime());
                $item->setAutor($accionista);
                $storage->save($item);
                
                // Tenemos que rechazar al accionista
                $this->abandonarState($accionista, 'av', 'reject');
                
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "new_resources"     [POST] /desertions

    public function putAction($id) { // Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "put_resource"      [PUT] /desertions/{id}

    public function deleteAction($id) { // DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_resource"      [DELETE] /desertions/{id} 

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

    private function abandonarState($accionista, $discr, $action) {
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        $apps = array
            (
            /*'voto' => 'AppVoto',
            'foro' => 'AppForo',
            'derecho' => 'AppDerecho',*/
            'av' => 'AppAV'
        );

        $actions = array
            (
            /*'pending' => 'pendiente',
            'public' => 'publica',
            'retornate' => 'retorna',*/
            'reject' => 'rechaza'
        );
        if (!isset($apps[$discr])) {
            throw new Exception('Not App');
        }
        if (!isset($actions[$action])) {
            throw new Exception('Not Action');
        }

        foreach ($accionista->getApp() as $app) {
                if ($app::nameClass == $apps[$discr] && $action == 'reject') {
                    // Self State
                    if ($app->getState() == StateClass::stateReject)
                        throw new Exception("No change to self state");
                    //
                    //$item, self::stateReject, self::actionReject, $comments
                    $app->setState(StateClass::stateReject);
                    $storage->save($app);
                }
            }
            
        return true;
    }
    
}
