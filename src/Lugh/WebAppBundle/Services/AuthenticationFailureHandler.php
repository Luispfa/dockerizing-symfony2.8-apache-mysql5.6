<?php
namespace Lugh\WebAppBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        
       $isWebService = $request->get('webservice',null);
        
       if(1 == $isWebService) {
           $json = json_encode(array('success'=>0));
           return new Response($json);
        }

        //default redirect operation.
        return parent::onAuthenticationFailure($request, $exception);

    }
}