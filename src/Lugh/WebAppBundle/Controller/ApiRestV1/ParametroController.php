<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\Parameters;

 /**
 * @RouteResource("Parametro")
 */
class ParametroController extends Controller { 
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction() {
    	$storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
                $parametros =$storage->getParametros();
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($parametros, 'json'));
    }// "get_Parametros"     [GET] /parametros
    
    public function getAction($id) // GET parametros
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json')); 
    }// "get_parametros"      [GET] /parametros/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postAction() // Create parametros
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        
        $request = $this->get('request');
        
        try {
            $parametro = $builder->buildParametro();
            $parametro->setKeyParam($request->get('key',''));
            $parametro->setValueParam($request->get('value',''));
            $parametro->setObservaciones($request->get('observaciones',''));
            $storage->save($parametro);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $parametro), 'json')); 
    }// "new_parametros"     [POST] /parametros
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update parametros
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        $request = $this->get('request');
        
        try {
            $parametro = $storage->getParametro($id);
            $parametro->setValueParam($request->get('value',''));
            $parametro->setObservaciones($request->get('observaciones',''));
            $storage->save($parametro);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $parametro), 'json')); 
    }// "put_parametro"      [PUT] /parametros/{id}
    
    public function deleteAction($id) // DELETE parametro
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json')); 
    } // "delete_resource"      [DELETE] /parametros/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json')); 
    } // "get_parametro_comments"     [GET] /parametros/{slug}/comments/{id}
}