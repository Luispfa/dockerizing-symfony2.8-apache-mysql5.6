<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

 /**
 * @RouteResource("Adhesion")
 */
class AdhesionController extends Controller {

    
    public function cgetAction()
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $adhesionProposals = $storage->getAdhesionProposals();
            $adhesionInitiatives = $storage->getAdhesionInitiatives();
            $adhesionRequests = $storage->getAdhesionRequests();
            $adhesionOffers = $storage->getAdhesionOffers();
            $items = array
                    ( 
                        'adhesionProposals'     =>  $adhesionProposals, 
                        'adhesionInitiatives'   =>  $adhesionInitiatives, 
                        'adhesionRequests'      =>  $adhesionRequests, 
                        'adhesionOffers'        =>  $adhesionOffers
                    );
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'proposals', 'offers', 'requests', 'initiatives'))));
    }// "get_resources"     [GET] /adhesions
    
    
    public function getAction($state) // GET Resource
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        $states = array
                (
                    'pending'   =>  StateClass::statePending,
                    'public'    =>  StateClass::statePublic,
                    'retornate' =>  StateClass::stateRetornate,
                    'reject'    =>  StateClass::stateReject,
                );
        if (!isset($states[$state]))
        {
            return new Response($serializer->serialize(array('error' => 'Not State'), 'json'));
        }
         try {
            $adhesionProposals = $storage->getAdhesionProposalsByState($states[$state]);
            $adhesionInitiatives = $storage->getAdhesionInitiativesByState($states[$state]);
            $adhesionRequests = $storage->getAdhesionRequestsByState($states[$state]);
            $adhesionOffers = $storage->getAdhesionOffersByState($states[$state]);
            $items = array
                    ( 
                        'adhesionProposals'     =>  $adhesionProposals, 
                        'adhesionInitiatives'   =>  $adhesionInitiatives, 
                        'adhesionRequests'      =>  $adhesionRequests, 
                        'adhesionOffers'        =>  $adhesionOffers
                    );
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "get_resource"      [GET] /adhesions/{state}
    
    public function postAction() // Create Resource
    {$security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_resources"     [POST] /items
    
    public function putAction($id) // Update Resource
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_resource"      [PUT] /resources/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    
}

