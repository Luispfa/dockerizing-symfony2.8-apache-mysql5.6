<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;

 /**
 * @RouteResource("User")
 */
class UserController extends Controller {
    
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_users"     [GET] /users
    
    public function getAction($id) // GET User
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_user"      [GET] /users/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function useridAction() // GET Resource
    {
        $serializer = $this->container->get('jms_serializer');
        try {
            $user = $this->getUser();
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($user, 'json',SerializationContext::create()->setGroups(array('Default')))); 
    }// "get_Accionista"      [GET] /users/userid
    
    public function postAction() // Create User
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_users"     [POST] /users
    
    public function putAction($id) // Update User
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_user"      [PUT] /users/{id}
    
    public function deleteAction($id) // DELETE User
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_user"      [DELETE] /user/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_user_comments"     [GET] /users/{slug}/comments/{id}
    
}

