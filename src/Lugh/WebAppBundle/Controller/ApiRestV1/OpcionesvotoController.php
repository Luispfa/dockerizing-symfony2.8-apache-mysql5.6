<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Opcionesvoto")
 */
class OpcionesvotoController extends Controller {
    
    
    public function cgetAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $opcionesVoto = $storage->getOpcionesVotos();
            $items = array('opcionesVoto' => $opcionesVoto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "get_opcionesvotos"     [GET] /opcionesvotos
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $opcionesVoto = $storage->getOpcionesVoto($id);
            $items = array('opcionesVoto' => $opcionesVoto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));

    }// "get_resource"      [GET] /opcionesvotos/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        try {       
           $opcionesVoto = $builder->buildOpcionesVoto();
           $opcionesVoto->setNombre($request->get('nombre',''));
           $opcionesVoto->setOrden($request->get('orden',0));
           $opcionesVoto->setSymbol($request->get('symbol',''));
           $storage->save($opcionesVoto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $opcionesVoto), 'json', SerializationContext::create()->setGroups(array('Default'))));
        
    }// "new_opcionesvotos"     [POST] /opcionesvotos
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $request = $this->get('request');
        try {       
           $opcionesVoto = $storage->getOpcionesVoto($id);
           $opcionesVoto->setNombre($request->get('nombre',$opcionesVoto->getNombre()));
           $opcionesVoto->setOrden($request->get('orden',$opcionesVoto->getOrden()));
           $opcionesVoto->setSymbol($request->get('symbol',$opcionesVoto->getSymbol()));
           $storage->save($opcionesVoto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $opcionesVoto), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "put_resource"      [PUT] /opcionesvotos/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_resource_comments"     [GET] /opcionesvotos/{slug}/comments/{id}
    
}

