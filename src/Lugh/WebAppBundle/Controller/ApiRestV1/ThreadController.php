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
 * @RouteResource("Thread")
 */
class ThreadController extends Controller {
    
    
    public function cgetAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $threads = $storage->getThreads();
            $items = array('threads' => $threads);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default','messages'))));
    }// "get_resources"     [GET] /threads
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $thread = $storage->getThread($id);
            $items = array('threads' => $thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'messages', 'documents'))));
    }// "get_resource"      [GET] /threads/{id}
    
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $user = $this->getUser();
        $request = $this->get("request");
        try {
           $accionista = $user->getAccionista();
           $item = $builder->buildThread();
           $item->setDateTime(new \DateTime());
           $item->setAutor($accionista);
           $item->setSubject($request->get('subject',''));
           $item->setBody($request->get('body',''));

           
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_resources"     [POST] /threads
    
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_resource"      [PUT] /resources/{id}
    
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
           $item = $builder->buildThread();
           $item->setDateTime(new \DateTime());
           $item->setAutor($accionista);
           $item->setSubject($request->get('subject',''));
           $item->setBody($request->get('body',''));
           
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        
        
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
        
    } // "new_resources"    [POST] /threads/tests
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    public function getStateAction($state)
    {
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
            $threads = $storage->getThreadsByState($states[$state]);
            $items = array('threads' => $threads);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default','messages'))));
    } // "get_resource_comments"     [GET] /threads/{state}/state
    
    public function getMessageAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $thread = $storage->getThread($id);
            $messages = $thread->getMessages();
            $items = array('messages' => $messages);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $messages), 'json',SerializationContext::create()->setGroups(array('Default'))));
    } // "get_user_comments"   [GET] /threads/{$id}/message
    
    public function putMessageAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        //return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
        $request = $this->get('request');
        $builder = $this->get('lugh.server')->getBuilder();
        $storage = $this->get('lugh.server')->getStorage();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        
        try {
            $thread = $storage->getThread($id);
            $message = $builder->buildMessage();
            $message->setAutor($user);
            $message->setBody($request->get('message', ''));
            $message->setDateTime(new \DateTime());
            if(($token = $request->get('token',false)) && $token != ''){
                $documents = $storage->getDocumentsByToken($token);
                $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            $thread->addMessage($message);
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $message), 'json',SerializationContext::create()->setGroups(array('Default'))));
    } // "post_user_comments"   [POST] /threads/{$id}/message
    
    
    public function putPendingAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $thread->pendiente();
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/pending
    

    public function putPublicAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            $thread->publica();
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/public
    

    public function putRetornateAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            $thread->retorna();
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/retornate
 
    

    public function putRejectAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            $thread->rechaza();
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/reject
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putLockedAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            $thread->locked();
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/locked
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putUnlockedAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer =  $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        try {
            $thread = $storage->getThread($id);
            $request = $this->get('request');
            $thread->unlocked();
            if ($request->get('message', false))
            {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if(($token = $request->get('token',false)) && $token != ''){
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $thread->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $thread->addMessage($message);
            }
            $storage->save($thread);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $thread), 'json',SerializationContext::create()->setGroups(array('Default', 'messages'))));
        
    }// "put_resource"      [PUT] /threads/{id}/reject

    private function setDocumentsOwnerMessage($documents, $user, $message)
    {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $document->setMessage($message);
                $storage->addStack($document);
            }
            $storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        
    }
}

