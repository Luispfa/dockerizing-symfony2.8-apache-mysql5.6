<?php
namespace Lugh\WebAppBundle\DomainLayer\Mail;
use Symfony\Component\Config\Definition\Exception\Exception;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mailer
 *
 * @author a.navarro
 */
class Mailer {
    
    const workFlowOn    = 1;
    const workFlowOff   = 0;
    
    private $transport;
    private $from;
    private $bcc;


    public function __construct($transport, $from, $bcc) {
        $this->transport = $transport;
        $this->from = $from;
        $this->bcc = $bcc;
    }
    
    public function send(\Swift_Message $message) {
        
        try {
            $mailer = \Swift_Mailer::newInstance($this->transport);
            if ($message->getFrom() == null)
            {
                $message->setFrom(array($this->from => $this->from));
            }
            if ($message->getBcc() == null)
            {
                $message->setBcc($this->bcc);
            }
            $result = $mailer->send($message);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    
    public function getFrom()
    {
        return $this->from;
    }
}
