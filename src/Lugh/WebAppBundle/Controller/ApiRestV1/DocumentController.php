<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


 /**
 * @RouteResource("Document")
 */
class DocumentController extends Controller {
    
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_docuemnts"     [GET] /documents
    
    public function getAction($id) // GET Document
    {
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        
        $documentObject = $em->getRepository('Lugh\WebAppBundle\Entity\Document')->findOneById($id);

        $isOwner = $user->getId() == $documentObject->getOwner()->getId(); 
        
        $isCommunique = $documentObject->getCommunique() != null;
        
        if($user->isAdmin() || $isOwner || $isCommunique){
        
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');

            try {
                $document = $storage->getDocument($id);
                $response = $document->getData();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $response;
        }
        else{
            return $this->redirect('/lugh/logic/web');
        }
            
    }// "get_docuemnt"      [GET] /documents/{id}
    
    public function postAction() // Create Document
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_docuemnts"     [POST] /documents
    
    public function putAction($id) // Update Document
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_docuemnt"      [PUT] /documents/{id}
    
    public function deleteAction($id) // DELETE Document
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_docuemnt"      [DELETE] /document/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_docuemnt_comments"     [GET] /documents/{slug}/comments/{id}
    
}

