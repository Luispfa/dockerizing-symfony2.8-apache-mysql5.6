<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    
    /**
     * @Route("/", name="_home")
     * @Template()
     */
    public function indexAction()
    {
        $this->get('request')->getSession()->set('admin_login', false);
                
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
        if ($this->get('lugh.Time')->inTime())
        {
            if ($security->isGranted('ROLE_USER_FULL'))
            {
                return $this->redirect($this->generateUrl('workspace_homepage'));
            }
            if ($security->isGranted('ROLE_USER_CERT'))
            {
                return $this->redirect($this->generateUrl('workspace_homepage'));
            }
            if ($security->isGranted('ROLE_USER_RET'))
            {
                return $this->redirect('home#/app/retornar');
            }
            if ($security->isGranted('ROLE_USER_PEN'))
            {
                return $this->redirect('home#/app/pendiente');
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_logout'));
    }
}
