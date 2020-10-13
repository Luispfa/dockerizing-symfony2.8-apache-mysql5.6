<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lugh\WebAppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ResettingController as FOSResettingController;
use \Symfony\Component\HttpFoundation\Request as Request;

/**
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends FOSResettingController
{
    

    /**
     * Reset user password
     */
    public function resetAction($token)
    {
        $request = Request::createFromGlobals();
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }
        
        if ($request->get('reset', false)) {
            $this->onSuccess($user);
            $response = new RedirectResponse($this->container->get('router')->generate('fos_user_security_logout'));
            return $response;
        }

        return $this->container->get('templating')->renderResponse('LughWebAppBundle:Resetting:reset.html.'.$this->getEngine(), array(
            'token' => $token
        ));
    }
    
    private function onSuccess($user)
    {
        $mailer = $this->container->get('lugh.server')->getMailer();
        $user->setPlainPassword(substr(md5(time()),0,8));
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setEnabled(true);
        $this->container->get('fos_user.user_manager')->updateUser($user);
        $mailer->formatandsend($user, 'resetPassword');
    }
    
    

}
