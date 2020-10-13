<?php
namespace Lugh\WebAppBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
   public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
       $isWebService = $request->get('webservice',null);
        
       if(1 == $isWebService) {
           $json = json_encode(array('success'=>1));
           return new Response($json);
        }

        //default redirect operation.
        return parent::onAuthenticationSuccess($request, $token);
    }

}