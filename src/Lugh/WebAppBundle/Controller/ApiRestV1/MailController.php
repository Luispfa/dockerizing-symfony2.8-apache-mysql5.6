<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;

 /**
 * @RouteResource("Mail")
 */
class MailController extends Controller {
    
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_mails"     [GET] /mails
    
    public function getAction($id) // GET Mail
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "get_mail"      [GET] /mails/{id}
    
    public function postToadminAction() // Create Mail
    {
        
        $valid = $this->checkHeaders();
        
        if($valid){
        
            $serializer = $this->container->get('jms_serializer');
            $mailer = $this->get('lugh.server')->getMailer();
            $request = $this->get('request');
            $body = html_entity_decode($request->get('body',''));
            $body = nl2br(strip_tags($request->get('body',''), '<br><br/><ul><li>'));

            $body .= '<br/><br/>';
            $body .= 'Name: ' . $this->getUser()->getAccionista()->getName();
            $body .= '<br/>';
            $body .= 'Doc: ' . $this->getUser()->getAccionista()->getDocumentNum();
            $body .= '<br/>';
            $body .= 'Email: ' . $this->getUser()->getEmail();

            $subject = $request->get('subject','');
            $from = $request->get('from','');
            try {
                $mailer->sendMailByRole(array('ROLE_CUSTOMER', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN'), $body, $subject, $from);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
            
        }
        else{
            return $this->redirect($this->get('request')->headers->get('host'));
        }
        
    }// "new_mails"     [POST] /mails/toadmins
    
    public function postTosuperAction() // Create Mail
    {
        $valid = $this->checkHeaders();
        
        if($valid){
        
            $serializer = $this->container->get('jms_serializer');
            $mailer = $this->get('lugh.server')->getMailer();
            $request = $this->get('request');
            $body = html_entity_decode($request->get('body',''));
            $body = nl2br(strip_tags($request->get('body',''), '<br><br/><ul><li>'));

            $body .= '<br/><br/>';
            $body .= 'Username: ' . $this->getUser()->getUsername();
            $body .= '<br/>';
            $body .= 'Email: ' . $this->getUser()->getEmail();

            $subject = $request->get('subject','');
            $from = $request->get('from','');
            try {
                $mailer->sendMailByRole(array('ROLE_SUPER_ADMIN'), $body, $subject, $from);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
            
        }
        else{
            return $this->redirect($this->get('request')->headers->get('host'));
        }
        
    }// "new_mails"     [POST] /mails/tosupers

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postToemailAction(){
        
        $valid = $this->checkHeaders();
        
        if($valid){
        
            $serializer = $this->container->get('jms_serializer');
            $mailer = $this->get('lugh.server')->getMailer();
            $request = $this->get('request');
            $body = html_entity_decode($request->get('body',''));
            $body = nl2br(strip_tags($request->get('body',''), '<br><br/><ul><li>'));
            $subject = $request->get('subject','');
            $from = $request->get('from','');
            try {
                $mailer->sendMailByAddress($request->get('to',array()), $body, $subject, $from);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
        
        }
        else{
            return $this->redirect($this->get('request')->headers->get('host'));
        }
        
    }// "new_mails"     [POST] /mails/toemails
    
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postToemailuserAction(){
        
        $valid = $this->checkHeaders();
        
        
        if($valid){
            
            $serializer = $this->container->get('jms_serializer');
            $mailer = $this->get('lugh.server')->getMailer();
            $request = $this->get('request');
        
            $body = html_entity_decode($request->get('body',''));
            $body = nl2br(strip_tags($request->get('body',''), '<br><br/><ul><li>'));
            $subject = $request->get('subject','');
            $from = $request->get('from','');
            try {
                $mailer->sendMailByAddress($request->get('to',array()), $body, $subject, $from);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));  
        }
        else{
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }// "new_mails"     [POST] /mails/toemailusers
    
    public function putAction($id) // Update Mail
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_mail"      [PUT] /mails/{id}
    
    public function deleteAction($id) // DELETE Mail
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_mail"      [DELETE] /mail/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_mail_comments"     [GET] /mails/{slug}/comments/{id}
    
    
    public function checkHeaders(){
        
        $request = $this->get('request');
        
        $host = $request->headers->get('host');
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        $valid = true;
        
        if($origin != null || $referer != null){
            
            if($origin != null && !strpos($origin, $host)){
                $valid = false;
            }
            if($referer != null && !strpos($referer, $host)){
                $valid = false;
            }
        }
        else{
            
            $valid = false;
        }
        
        return $valid && $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}

