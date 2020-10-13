<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Entity\User;

/**
 * @Route("/configusers")
 */
class UsersPlatformController extends Controller
{
    /**
     * @Route("/" ,name="_configusers_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    /**
     * @Route("/editusers" ,name="_configusers_edit_users")
     * @Template()
     */
    public function editUsersAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('Lugh\WebAppBundle\Entity\User')->findAll();
        
        $users_platform = array();
        
        foreach ($users as $user) {
            if ($user->getAccionista() == null)
            {
                $users_platform[] = $user;
            }
        }

        $form_users = array();
        $form_users['username'] = $this->getWidget(
                'text', 
                'username', 
                ''
                );
        $form_users['password'] = $this->getWidget(
                'password', 
                'password', 
                ''
                );
        $form_users['email'] = $this->getWidget(
                'email', 
                'email', 
                ''
                );
        //die(var_dump($parameters_time));
        return array('users_platform' => $users_platform, 'form_users' => $form_users, 'platform' => $platform);
    }
    
    /**
     * @Route("/adduser" ,name="_configplatform_add_user")
     * @Template()
     */
    public function addUserAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $params = $request->get('params');
        $form = $params['form'];
        $user = new User();
        try {
            if (!isset($form['username']) || $form['username']=='')
            {
                throw new Exception("Username Blank");
            }
            if (!isset($form['password']) || $form['password']=='')
            {
                throw new Exception("Password Blank");
            }
            if (!isset($form['email']) || $form['email']=='')
            {
                throw new Exception("Email Blank");
            }
            $user->setUsername($form['username']);
            $user->setPlainPassword($form['password']);
            $user->setEmail($form['email']);
            $user->setRoles(array('ROLE_CUSTOMER'));
            $user->setEnabled(true);
            $user->setDateTime(new \DateTime());
            $em->persist($user);
            $em->flush();
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/edituser" ,name="_configplatform_edit_user")
     * @Template()
     */
    public function editUserAction()
    {

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $params = $request->get('params');

        $form = $params['form'];
        $user = $em->getRepository('Lugh\WebAppBundle\Entity\User')->find($request->get('id'));

        $changes = false;
        try {
            if (isset($form['username']) && $form['username']!=''){
                $user->setUsername($form['username']);
                $changes = true;
            }
            if (isset($form['email']) && $form['email']!=''){
                $user->setEmail($form['email']);
                $changes = true;
            }
            if($changes){
                $em->persist($user);
                $em->flush();
            }

        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/removeuser" ,name="_configplatform_remove_user")
     * @Template()
     */
    public function removeUserAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        try {
            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\User')->find($request->get('id'));
            $em->remove($parameter);
            $em->flush();
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    
    private function getWidget($type, $name, $data, $extra=array())
    {
 
        $widget = $this->createFormBuilder()->add(
                $name, 
                $type, 
                array_merge(array(
                    'data'      => $data,
                    'required'  => false
                ),$extra))->getForm()->createView();
        
        return $widget;
    }
    
    
}
