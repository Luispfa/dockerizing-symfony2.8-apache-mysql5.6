<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("AbsAdicional")
 */
class AbsAdicionalController extends Controller {
    
    
    public function cgetAction()
    {
        $request = $this->get('request');
        //die(var_dump($request->getLocale()));
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $absAdicional = $storage->getAbsAdicionals();
            $items = array('absAdicional' => $absAdicional);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'tipoVoto', 'opcionesVoto'))));
    }// "get_absadicionals"     [GET] /absadicionals

    public function getTipoAction($id)
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $absAdicional = $storage->getTipoAbsAdicional($id);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($absAdicional, 'json', SerializationContext::create()->setGroups(array('Default', 'tipoVoto', 'opcionesVoto'))));
    }// "get_puntos"     [GET] /absadicionals/{id}/tipo
    
    public function getAction($id) // GET Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));

    }// "get_resource"      [GET] /puntos/{id}
    
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_puntos"     [POST] /puntos
    
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_resource"      [PUT] /puntos/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    
}

