<?php

namespace Lugh\WebAppBundle\Lib;

class SendMailClass {
    
    static private $avisos_email_from = "solicitud@header.net";
    static private $caixa_email_from = "votoelectronico@caixabank.com";
    static private $avisos_email_user = "lev002c";
    static private $caixa_email_user = "";
    static private $avisos_email_password = "UsuarioA443";
    static private $caixa_email_password = "";
    static private $avisos_email_port = 25;
    static private $avisos_email_server = 'smtp.header.net';
    static private $caixa_email_server = 'localhost';
    static private $avisos_email_transport = 1;
    static private $itemID = null;
    
    static public function sendMail($to, $subject= '', $body = '', $itemId=null, $adjuntos=null)
    {    
        //self::setParameters();
        
        // Create the Transport
        $transport = \Swift_MailTransport::newInstance();
        
        switch (intval(self::$avisos_email_transport)) {
            case 0:
                $transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
                break;
            case 1:
                $transport = \Swift_SmtpTransport::newInstance(self::$avisos_email_server, intval(self::$avisos_email_port))
                    ->setUsername(self::$avisos_email_user)
                    ->setPassword(self::$avisos_email_password);
                break;
            default:
                $transport = \Swift_MailTransport::newInstance();
                break;
        }

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Create a message
        $message = \Swift_Message::newInstance($subject)
          ->setFrom(array(self::$avisos_email_from => self::$avisos_email_from))
          ->setTo($to)
          ->setBody($body,'text/html')
          ;
        
        //  Attach files in array adjuntos
        if ($adjuntos != null)
        {
            foreach ($adjuntos as $key=>$value) {
                $attachement = \Swift_Attachment::fromPath($value)
                        ->setFilename($key);
                $message->attach($attachement);
            }
            
        }

        // Send the message
        try{
         $result = $mailer->send($message);
        }
        catch(\Swift_TransportException $e) {
            echo $e->getMessage();
        }

        
        self::$itemID = null;
        
    }
    
    
    static public function sendMailCaixa($to, $subject= '', $body = '', $itemId=null, $adjuntos=null)
    {    
        //self::setParameters();
        
        // Create the Transport
        $transport = \Swift_MailTransport::newInstance();
        
        switch (intval(self::$avisos_email_transport)) {
            case 0:
                $transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
                break;
            case 1:
                $transport = \Swift_SmtpTransport::newInstance(self::$caixa_email_server, intval(self::$avisos_email_port))
                    ->setUsername(self::$caixa_email_user)
                    ->setPassword(self::$caixa_email_password);
                break;
            default:
                $transport = \Swift_MailTransport::newInstance();
                break;
        }

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);
        
        // Create a message
        $message = \Swift_Message::newInstance($subject)
          ->setFrom(array(self::$caixa_email_from => self::$caixa_email_from))
          ->setTo($to)
          ->setBody($body,'text/html')
          ;
        
        //  Attach files in array adjuntos
        if ($adjuntos != null)
        {
            foreach ($adjuntos as $key=>$value) {
                $attachement = \Swift_Attachment::fromPath($value)
                        ->setFilename($key);
                $message->attach($attachement);
            }
            
        }

        // Send the message
        try{
         $result = $mailer->send($message);
         var_dump($result);
        }
        catch(\Swift_TransportException $e) {
            var_dump("meh");
            echo $e->getMessage();
        }

        
        self::$itemID = null;
        
    }
    
}