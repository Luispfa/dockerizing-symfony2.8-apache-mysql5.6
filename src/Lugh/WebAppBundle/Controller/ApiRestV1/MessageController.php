<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Message")
 */
class MessageController extends Controller {
    
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_messages"     [GET] /messages
    
    public function getAction($id) // GET Message
    {
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $message = $storage->getMessage($id);
            $items = array('messages' => $message);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default','documents'))));
    }// "get_message"      [GET] /messages/{id}
    
    public function postAction() // Create Message
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_messages"     [POST] /messages
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update Message
    { 
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $message = $storage->getMessage($id);
            $message->setBody($request->get('body',''));
            if(($token = $request->get('token',false)) && $token != ''){
                $documents = $storage->getDocumentsByToken($token);
                $this->setDocumentsOwnerMessage($documents, $message->getAutor(), $message);
            }
            $storage->save($message);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $message), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "put_message"      [PUT] /messages/{id}
    
    public function deleteAction($id) // DELETE Message
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_message"      [DELETE] /message/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_message_comments"     [GET] /messages/{slug}/comments/{id}
    
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

