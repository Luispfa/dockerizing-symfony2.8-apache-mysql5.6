<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    
    /**
     * @Route("/user/", name="_user")
     * @Template()
     */
    public function indexAction()
    {
        $this->get('request')->getSession()->set('admin_login', true);
        
        $security = $this->get('security.context');
        
        if ($security->isGranted('ROLE_SUPER_ADMIN'))
        {
            return $this->redirect($this->generateUrl('admin_workspace'));
        }
        if ($security->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('admin_workspace'));
        }
        if ($security->isGranted('ROLE_CUSTOMER'))
        {
            return $this->redirect($this->generateUrl('admin_workspace'));
        }
        
        return $this->redirect($this->generateUrl('fos_user_security_logout'));
    }
}
