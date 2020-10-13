<?php

namespace Lugh\WebAppBundle\DomainLayer\Mail;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class ResetMailer implements MailerInterface
{
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct(RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        /*$template = $this->parameters['resetting.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url
        ));
        $this->sendEmailMessage($rendered, $user->getEmail());*/
        $mailer = $this->getKernel()->getContainer()->get('lugh.server')->getMailer();
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $mailer->formatandsend($user, 'forgotPassword', '', array('%user%' => $user,'%confirmationUrl%' => $url));
    }
    
    private function getKernel()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel;
    }

}