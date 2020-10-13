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
 * @RouteResource("Logmail")
 */
class LogmailController extends Controller {
    
    
    public function cgetAction()
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $logmails = $storage->getLogMails();
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($logmails, 'json',SerializationContext::create()->setGroups(array('Default','Notification', 'Subject')))); 
    }// "get_logmails"     [GET] /logmails
    
    public function getAction($id) // GET LogMail
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        try {
            $logmail = $storage->getLogMail($id);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($logmail, 'json',SerializationContext::create()->setGroups(array('Default','Mail')))); 
    }// "get_logmail"      [GET] /logmails/{id}
    
    public function postAction() // Create LogMail
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "new_Logmails"     [POST] /logmails
    
    public function putAction($id) // Update LogMail
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_logmail"      [PUT] /logmails/{id}
    
    public function deleteAction($id) // DELETE LogMail
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_logmail"      [DELETE] /logmail/{id} 
    
    public function cgetWorkflowAction($id)
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            if ($id == 'mine')
            {
                $user = $this->getUser();
            }
            else
            {
                $user = $storage->getUser($id);
            }
            $logmails = $storage->getLogMailsUserWorkflow($user,true);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($logmails, 'json',SerializationContext::create()->setGroups(array('Default','Notification', 'Subject')))); 
    }// "get_resources"     [GET] /logmails/{id}/workflow
    
    public function cgetInAction($id)
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            if ($id == 'mine')
            {
                $user = $this->getUser();
                $logmails = $storage->getLogMailsUserWorkflow($user,false,'in');
            }
            elseif($id == 'all'){
                $logmails = array();
                $user = $this->getUser();
                $costumers = $this->getUsersByRole("ROLE_CUSTOMER");
                $admin = $this->getUsersByRole("ROLE_SUPER_ADMIN");
                $platformEmail = $this->get('lugh.parameters')->getByKey('Config.mail.from', 'default');

                
                if(in_array($user->getEmail(), $costumers) || in_array($user->getEmail(), $admin)){
                
                    $logmailsprov = $storage->getLogMails();
                    foreach($logmailsprov as $logmail){
                        $to = $logmail->getMailTo();
                        $hide = $logmail->getHide();
                        $wf = $logmail->getWf();
                        if((in_array($to,$costumers) || in_array($to, $admin) || $platformEmail == $to ) && $hide == 0 && $wf == 0){
                            $logmails[] = $logmail;
                        }
                    }
                }
                else{
                    //$user = $this->getUser();
                    //$logmails = $storage->getLogMailsUserWorkflow($user,false,'in');
                    $logmailsprov = $storage->getLogMails();
                    foreach($logmailsprov as $logmail){
                        $to = $logmail->getMailTo();
                        $hide = $logmail->getHide();
                        $wf = $logmail->getWf();
                        if($user->getEmail() == $to && $hide == 0 && $wf == 0){
                            $logmails[] = $logmail;
                        }
                    }
                }
            }
            else
            {
                $user = $storage->getUser($id);
                $logmails = $storage->getLogMailsUserWorkflow($user,false,'in');
            }
            
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($logmails, 'json',SerializationContext::create()->setGroups(array('Default', 'Mail', 'UserFrom','UserTo')))); 
    }// "get_resources"     [GET] /logmails/{id}/in
    
    public function cgetOutAction($id)
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();

        try {
            if ($id == 'mine')
            {
                $user = $this->getUser();
                $logmails = $storage->getLogMailsUserWorkflow($user,false,'out');
            }
            elseif($id == 'all'){
                $logmails = array();
                $user = $this->getUser();
                $costumers = $this->getUsersByRole("ROLE_CUSTOMER");
                $admin = $this->getUsersByRole("ROLE_SUPER_ADMIN");
                $platformEmail = $this->get('lugh.parameters')->getByKey('Config.mail.from', 'default');
                
                if(in_array($user->getEmail(), $costumers) || in_array($user->getEmail(), $admin)){
                    $logmailsprov = $storage->getLogMails();
                    foreach($logmailsprov as $logmail){
                        $from = null;
                        if($logmail->getUserFrom() != null && $logmail->getUserFrom() != ''){
                            $from = $logmail->getUserFrom()->getEmail();
                        }
                        $hide = $logmail->getHide();
                        $wf = $logmail->getWf();
                        
                        if((in_array($from,$costumers) || in_array($from, $admin) || $platformEmail == $from ) && $hide == 0 && $wf == 0){
                            $logmails[] = $logmail;
                        }
                    }
                }
                else{
                   $user = $this->getUser();
                    $logmails = $storage->getLogMailsUserWorkflow($user,false,'out');
                }
            }
            else
            {
                $user = $storage->getUser($id);
                $logmails = $storage->getLogMailsUserWorkflow($user,false,'out');
            }
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($logmails, 'json',SerializationContext::create()->setGroups(array('Default', 'Mail', 'UserFrom','UserTo')))); 
    }// "get_resources"     [GET] /logmails/{id}/out
    
    private function getUsersByRole($varRole)
    {
        $em = $this->get('doctrine')->getManager();
        
        $allowedUsers = array();

        $entities = $em->getRepository('Lugh\WebAppBundle\Entity\\User')->findAll();


        foreach($entities as $user){
            
            foreach($user->getRoles() as $role){
                
                if($role == $varRole){
                        
                       $allowedUsers[] = $user->getEmail();
                       break;
                }

            }
        }
        return $allowedUsers;
    }
    
}

