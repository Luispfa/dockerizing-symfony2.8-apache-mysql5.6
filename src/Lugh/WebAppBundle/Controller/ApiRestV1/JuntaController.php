<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Lugh\WebAppBundle\Annotations\Permissions;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

 /**
 * @RouteResource("Junta")
 */
class JuntaController extends Controller {
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage    = $this->get('lugh.server')->getStorage();
        try {
            $junta = $storage->getJunta();
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($junta, 'json',SerializationContext::create()->setGroups(array('Default')))); 

    }// "get_resources"     [GET] /juntas
    
    
    public function getAction($id) // GET Resource
    {
        $serializer = $this->container->get('jms_serializer');
		return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_resource"      [GET] /resources/{id}
    
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
		return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_resources"     [POST] /resources
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($state) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $states = array
                (
                    StateClass::stateConfiguracion  => 'configuracion',
                    StateClass::stateConvocatoria   => 'convocatoria',
                    StateClass::statePrejunta       => 'prejunta',
                    StateClass::stateAsistencia     => 'asistencia',
                    StateClass::stateQuorumCerrado  => 'quorumcerrado',
                    StateClass::stateVotacion       => 'votacion',
                    StateClass::stateFinalizado     => 'finalizado'
                );
        if (!isset($states[$state]))
        {
            return new Response($serializer->serialize(array('error' => 'Not State'), 'json'));
        }
        
        try {
            $junta = $storage->getJunta();
            $junta->{$states[$state]}();
            $storage->save($junta, false);
        } catch (\Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $junta), 'json',SerializationContext::create()->setGroups(array('Default'))));

    }// "put_resource"      [PUT] /juntas/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putForceAction($state, $active) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $states = array
                (
                    'acreditacion'=> 'setAcreditacionEnabled',
                    'votacion'    => 'setVotacionEnabled',
                    'preguntas'   => 'setPreguntasEnabled',
                    'live'        => 'setLiveEnabled',
                    'abandono'    => 'setAbandonoEnabled'
                );
        if (!isset($states[$state]))
        {
            return new Response($serializer->serialize(array('error' => 'Not Method'), 'json'));
        }
        
        try {
            $junta = $storage->getJunta();
            $junta->{$states[$state]}($active);
            $storage->save($junta, false);
        } catch (\Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $junta), 'json',SerializationContext::create()->setGroups(array('Default'))));

    }// "put_resource"      [PUT] /juntas/{id}/force/{active}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
		return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
}

