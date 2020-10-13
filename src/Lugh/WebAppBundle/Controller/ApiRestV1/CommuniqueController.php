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
 * @RouteResource("Communique")
 */
class CommuniqueController extends Controller {
    
    
    public function cgetAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $communiques = $storage->getCommuniques();
            $items = array('communiques' => $communiques);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "get_resources"     [GET] /communiques
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $communique = $storage->getCommunique($id);
            $items = array('communiques' => $communique);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents'))));
    }// "get_resource"      [GET] /communiques/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $user = $this->getUser();
        $request = $this->get('request');
        try {
            $item = $builder->buildCommunique();
            $item->setSubject($request->get('subject',''));
            $item->setBody($request->get('body',''));
            $item->setDateTime(new \DateTime());
            $item->setEnabled($request->get('enabled',false) ? 1 : 0);
            $item->setAutor($user);
            if(($token = $request->get('token',false)) && $token != ''){
                $documents = $storage->getDocumentsByToken($token);
                $this->setDocumentsOwnerCommunique($documents, $user, $item);
            }
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_resources"     [POST] /communiques
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $user = $this->getUser();
        $request = $this->get('request');
        try {
           $item = $storage->getCommunique($id);
           $item->setSubject($request->get('subject',''));
           $item->setBody($request->get('body',''));
           $item->setDateTime(new \DateTime());
           $item->setEnabled($request->get('enabled',false) ? 1 : 0);
           $item->setAutor($user);
           if(($token = $request->get('token',false)) && $token != ''){
                $documents = $storage->getDocumentsByToken($token);
                $this->setDocumentsOwnerCommunique($documents, $user, $item);
            }
           $storage->save($item);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "put_resource"      [PUT] /communiques/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putEnableAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        try {
            $communique = $storage->getCommunique($id);
            $communique->enable();
            $storage->save($communique);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $communique), 'json',SerializationContext::create()->setGroups(array('Default', 'Documents'))));
        
    }// "put_resource"      [PUT] /threads/{id}/enable
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putDisableAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        try {
            $communique = $storage->getCommunique($id);
            $communique->disable();
            $storage->save($communique);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $communique), 'json',SerializationContext::create()->setGroups(array('Default', 'Documents'))));
        
    }// "put_resource"      [PUT] /threads/{id}/disable

    private function setDocumentsOwnerCommunique($documents, $user, $communique)
    {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $document->setCommunique($communique);
                $storage->addStack($document);
            }
            $storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        
    }
}

