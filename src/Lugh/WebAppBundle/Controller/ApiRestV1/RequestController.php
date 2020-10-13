<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;

 /**
 * @RouteResource("Request")
 */
class RequestController extends Controller {
    
    
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
            $requests = $storage->getRequests();
            $items = array('requests' => $requests);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'NumAdhesion'))));
    }// "get_resources"     [GET] /requests
    
    public function getAction($id) // GET Resource
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $requests = $storage->getRequest($id);
            $items = array('requests' => $requests);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
    }// "get_resource"      [GET] /resources/{id}
    
    public function postTestAction() // Create Resource
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $this->get('lugh.mode')->setTest();
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $request = $this->get('request');
        $user = $this->getUser();
        try {
           $accionista = $user->getAccionista();
           $item = $builder->buildRequest();
           $item->setDateTime(new \DateTime());
           $item->setAutor($accionista);
           if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $item->addMessage($message);
            }
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_resources"     [POST] /requests/tests
    
    public function postAction() // Create Resource
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $request = $this->get('request');
        $user = $this->getUser();
        try {
           $accionista = $user->getAccionista();
           $item = $builder->buildRequest();
           $item->setDateTime(new \DateTime());
           $item->setAutor($accionista);
           $item->setDescription($request->get('description',''));
           $item->setTitle($request->get('title',''));
           if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $item->addMessage($message);
            }
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_resources"     [POST] /resources
    
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
    
    public function putTestAction($id, $state)
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $this->get('lugh.mode')->setTest();
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        
        $user = $this->getUser();
        $statesMethod = array
                (
                    'pending'   =>  'pendiente',
                    'public'    =>  'publica',
                    'retornate' =>  'retorna',
                    'reject'    =>  'rechaza',
                );
        if (!isset($statesMethod[$state]))
        {
            return new Response($serializer->serialize(array('error' => 'Not State'), 'json'));
        }
        
        try {
            $item = $storage->getRequest($id);
            $request = $this->get('request');
            $item->setDateTime(new \DateTime());
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $item->addMessage($message);
            }
            $item->{$statesMethod[$state]}($request->get('message', null));
            $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    } // "put_resource"     [PUT] /requests/{id}/tests/{state}
    
    public function putPendingAction($id) // Update Resource
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $item = $storage->getRequest($id);
            $request = $this->get('request');
            $item->setDateTime(new \DateTime());
            $item->setDescription($request->get('description',$item->getDescription()));
            $item->setTitle($request->get('title',$item->getTitle()));
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $item->addMessage($message);
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            $item->pendiente($request->get('message', null));
            $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /requests/{id}/pending
    
    public function putPublicAction($id) // Update Resource
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $itemRequest = $storage->getRequest($id);
            $request = $this->get('request');
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $itemRequest->addMessage($message);
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            $itemRequest->publica($request->get('message', null));
            $storage->save($itemRequest);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $itemRequest), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /requests/{id}/public
    
    public function putRetornateAction($id) // Update Resource
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $itemRequest = $storage->getRequest($id);
            $request = $this->get('request');
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $itemRequest->addMessage($message);
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            $itemRequest->retorna($request->get('message', null));
            $storage->save($itemRequest);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $itemRequest), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /requests/{id}/retornate
    
    public function putRejectAction($id) // Update Resource
    { 
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $itemRequest = $storage->getRequest($id);
            $request = $this->get('request');
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $itemRequest->addMessage($message);
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            $itemRequest->rechaza($request->get('message', null));
            $storage->save($itemRequest);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $itemRequest), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /requests/{id}/reject
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putMessageAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer  = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $itemRequest = $storage->getRequest($id);
            $request = $this->get('request');
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $itemRequest->addMessageWihtMail($message);
            }
            $storage->save($itemRequest);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $itemRequest), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "post_resource"      [POST] /offer/{id}/message
    
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
    
    public function getStateAction($state)
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
            $requests = $storage->getRequestsByState($states[$state]);
            $items = array('requests' => $requests);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    } // "get_resource_comments"     [GET] /requests/{state}/state
    
    public function getAdhesionsAction($id)
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $request = $storage->getRequest($id);
            $adhesions = $request->getAdhesions();
            $items = array('adhesions' => $adhesions);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    } // "get_resource_comments"     [GET] /requests/{id}/adhesions
    
    public function postAdhesionTestAction($id)
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $this->get('lugh.mode')->setTest();
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $builder = $this->get('lugh.server')->getBuilder();
        $storage = $this->get('lugh.server')->getStorage();
        $user = $this->getUser();
        
        try {
            $accionista = $user->getAccionista();
            $offer = $storage->getRequest($id);
            $adhesion = $builder->buildAdhesionRequest();
            $adhesion->setAccionista($accionista);
            $adhesion->setDateTime(new \DateTime());
            $offer->addAdhesion($adhesion);
            $storage->save($offer);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $adhesion), 'json',SerializationContext::create()->setGroups(array('Default'))));
    } // "post_user_comments"   [POST] /requests/{$id}/adhesions/tests
    
    public function postAdhesionAction($id)
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        //return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
        $request = $this->get('request');
        $builder = $this->get('lugh.server')->getBuilder();
        $storage = $this->get('lugh.server')->getStorage();
        $user = $this->getUser();
        
        try {
            $accionista = $user->getAccionista();
            $request = $storage->getRequest($id);
            $adhesion = $builder->buildAdhesionRequest();
            $adhesion->setDateTime(new \DateTime());
            $adhesion->setAccionista($accionista);
            $request->addAdhesion($adhesion);
            $storage->save($request);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $adhesion), 'json',SerializationContext::create()->setGroups(array('Default'))));
    } // "post_user_comments"   [POST] /requests/{$id}/adhesions
    
    public function getMessageAction($id)
    {
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_USER_PEN'))
        {
            return $this->redirect('/lugh/logic/web');
        }
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $request = $storage->getRequest($id);
            $messages = $request->getMessages();
            $items = array('messages' => $messages);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($messages, 'json',SerializationContext::create()->setGroups(array('Default'))));
    } // "get_user_comments"   [GET] /proposals/{$id}/message
    
}

