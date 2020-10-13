<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/public")
 */
class PublicController extends Controller
{
    
    /**
     * @Route("/delegation/{id}/{token}", name="_public_delegation")
     * @Template()
     */
    public function delegationAction($id,$token)
    {
         $storage = $this->get('lugh.server')->getStorage();
        try {       
           $delegacion = $storage->getDelegacionToken($id, $token);
           if ($delegacion->getState() != StateClass::statePending)
           {
               throw new Exception;
           }
        } catch (Exception $exc) {
            throw new NotFoundHttpException(sprintf('The delegado or item does not exist'));
        }
        return array('id' => $id, 'token' => $token);
    }
    
    /**
     * @Route("/delegation/confirm/{id}/{token}", name="_public_confirm_delegation")
     * @Template()
     */
    public function delegationConfirmAction($id,$token)
    {
        $storage = $this->get('lugh.server')->getStorage();
        try {       
           $delegacion = $storage->getDelegacionToken($id, $token);
           if ($delegacion->getState() != StateClass::statePending)
           {
               throw new NotFoundHttpException(sprintf('The delegado or item does not exist'));
           }
           $delegacion->publica();
           $storage->save($delegacion, false);
        } catch (Exception $exc) {
             throw new Exception($exc->getMessage());
        }
        $response = new RedirectResponse($this->container->get('router')->generate('fos_user_security_logout'));
        return $response;
    }
    
    /**
     * @Route("/delegation/reject/{id}/{token}", name="_public_reject_delegation")
     * @Template()
     */
    public function delegationRejectAction($id,$token)
    {
        $storage = $this->get('lugh.server')->getStorage();
        try {       
           $delegacion = $storage->getDelegacionToken($id, $token);
           if ($delegacion->getState() != StateClass::statePending)
           {
               throw new NotFoundHttpException(sprintf('The delegado or item does not exist'));
           }
           $delegacion->rechaza();
           $storage->save($delegacion, false);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        $response = new RedirectResponse($this->container->get('router')->generate('fos_user_security_logout'));
        return $response;
    }
}
