<?php
namespace Lugh\WebAppBundle\DomainLayer\Mail;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\DBAL\DBALException;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
class LughMailWF extends LughMail {
    
    
    public function workflow($item, $state, $extra = '', $attributs = array(), $external = array(), $attachments = array()) {
        return parent::formatandsend($item, $state, $extra, $attributs, $external, $attachments);
    }

}