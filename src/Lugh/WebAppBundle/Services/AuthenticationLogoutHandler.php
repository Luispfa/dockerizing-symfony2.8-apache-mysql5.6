<?php
namespace Lugh\WebAppBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthenticationLogoutHandler implements AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface
{
   
    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception) {
        return new RedirectResponse($this->getContainer()->get('router')->generate('fos_user_security_login_app'));
    }

    public function onLogoutSuccess(Request $request) {
        if ($request->getSession()->get('admin_login', false))
        {
            return new RedirectResponse($this->getContainer()->get('router')->generate('admin_logout'));
        }
        else
        {
            return new RedirectResponse($this->getContainer()->get('router')->generate('fos_user_security_login_app'));
        }
        
    }
    
    private function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }

}