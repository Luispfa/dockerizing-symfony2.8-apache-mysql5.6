<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Tipovoto")
 */
class TipovotoController extends Controller {
    
    
    public function cgetAction()
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $tipoVotos = $storage->getTipoVotos();
            $items = array('tipoVotos' => $tipoVotos);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'OpcionesVoto'))));
    }// "get_puntos"     [GET] /tipovotos

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAdminAction()
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $tipoVotos = $storage->getAdminTipoVotos();
            $items = array('tipoVotos' => $tipoVotos);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'OpcionesVoto'))));
    }// "get_puntos"     [GET] /tipovotos/admin
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $tipoVoto = $storage->getTipoVoto($id);
            $items = array('tipoVotos' => $tipoVoto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'OpcionesVoto'))));

    }// "get_resource"      [GET] /tipovotos/{id}

    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_puntos"     [POST] /tipovotos
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /tipovotos/{id} 
    
    
}

