<?php
namespace Lugh\WebAppBundle\DomainLayer\Mail;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\DBAL\DBALException;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
class LughMailNoWF extends LughMail {
    
    
    public function workflow($item, $state, $extra = '', $attributs = array(), $external = array(), $attachments = array()) 
    {
        return true;
    }
    

}