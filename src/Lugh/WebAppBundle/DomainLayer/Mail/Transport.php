<?php

namespace Lugh\WebAppBundle\DomainLayer\Mail;
use Symfony\Component\DependencyInjection\ContainerInterface as container;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Transport
 *
 * @author a.navarro
 */
class Transport extends LughService {
    
    const transport_sendmail    = 0;
    const transport_smtp        = 1;
    
    private $mail_bcc = array('anavarro@header.net');
    private $mail_from = "solicitud@header.net";
    private $mail_user = "lev002c";
    private $mail_password = "UsuarioA443";
    private $mail_port = 25;
    private $mail_server = 'smtp.header.net';
    private $mail_transport = 1;
    private $mail_ssl = 0;
    
    public function __construct(container $container = null) {
        parent::__construct($container);
        $this->mail_bcc         =   $this->get('lugh.parameters')->getByKey('Config.mail.bcc', json_encode($this->mail_bcc));
        $this->mail_from        =   $this->get('lugh.parameters')->getByKey('Config.mail.from', $this->mail_from);
        $this->mail_user        =   $this->get('lugh.parameters')->getByKey('Config.mail.user', $this->mail_user);
        $this->mail_password    =   $this->get('lugh.parameters')->getByKey('Config.mail.password', $this->mail_password);
        $this->mail_port        =   $this->get('lugh.parameters')->getByKey('Config.mail.port', $this->mail_port);
        $this->mail_server      =   $this->get('lugh.parameters')->getByKey('Config.mail.server', $this->mail_server);
        $this->mail_transport   =   $this->get('lugh.parameters')->getByKey('Config.mail.transport', $this->mail_transport);
        $this->mail_ssl         =   $this->get('lugh.parameters')->getByKey('Config.mail.ssl', $this->mail_ssl);
        }
    
    public function getMailerMethod()
    {
        
        switch (intval($this->mail_transport)) {
            case self::transport_sendmail:
                $transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -oi -t');                
                break;
            case self::transport_smtp:
                $transport = \Swift_SmtpTransport::newInstance($this->mail_server, intval($this->mail_port), $this->mail_ssl ? 'ssl' : null)
                    ->setUsername($this->mail_user)
                    ->setPassword($this->mail_password);
                break;
            default:
                $transport = \Swift_MailTransport::newInstance();
                break;
        }
        return $transport;
    }
    
    public function getMailerFrom()
    {
        return $this->mail_from;
    }
    
    public function getMailerBcc()
    {
        return json_decode($this->mail_bcc);
    }
}
