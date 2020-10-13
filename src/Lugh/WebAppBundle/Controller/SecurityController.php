<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends BaseController
{
       
    public function renderLogin(array $data) {
        
        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') && 
                !$this->container->get('security.context')->isGranted('ROLE_USER_RET') &&
                !$this->container->get('security.context')->isGranted('ROLE_USER_PEN')){
            // IS_AUTHENTICATED_FULLY also implies IS_AUTHENTICATED_REMEMBERED, but IS_AUTHENTICATED_ANONYMOUSLY doesn't
            return new RedirectResponse($this->container->get('router')->generate('_home', array())); 
        }

        $requestAttributes = $this->container->get('request')->attributes;
        $server = $this->container->get('request')->server;
        $title = 'Lugh Electronic Platforms';
        
        if ($requestAttributes->get('_route') == 'admin_login' || $requestAttributes->get('_route') == 'admin_login_user') {
            $template = sprintf('LughWebAppBundle:Security:loginUser.html.twig');
        } 
        elseif ($this->container->get('lugh.route.template')->isAdminAddr($server->get('HTTP_HOST'))) {
            $template = sprintf('LughWebAppBundle:Security:loginAdmin.html.twig');
        }
        else {
            $title = $this->container->get('lugh.parameters')->getByKey('Config.platform.title', 'Lugh Electronic Platforms');
            $template = $this->container->get('lugh.route.template')->getTemplate('/home/', 'index.html');
        }
        
        
        return $this->container->get('templating')->renderResponse($template, array_merge($data, array('title' => $title)));
    }
    
    
}
