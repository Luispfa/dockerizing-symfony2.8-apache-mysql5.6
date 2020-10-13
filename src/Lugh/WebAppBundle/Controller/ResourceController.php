<?php

namespace Lugh\WebAppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Resource")
 */
class ResourceController extends Controller {
    
    
    public function cgetAction()
    { 
    }// "get_resources"     [GET] /resources
    
    public function getAction($id) // GET Resource
    {

    }// "get_resource"      [GET] /resources/{id}
    
    public function postAction() // Create Resource
    {
        
    }// "new_resources"     [POST] /resources
    
    public function putAction($id) // Update Resource
    { 
        
    }// "put_resource"      [PUT] /resources/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        
    } // "get_resource_comments"     [GET] /resources/{slug}/comments/{id}
    
}

