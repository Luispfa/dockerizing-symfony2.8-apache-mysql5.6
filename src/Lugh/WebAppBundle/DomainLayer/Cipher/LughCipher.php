<?php
namespace Lugh\WebAppBundle\DomainLayer\Cipher;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilder
 *
 * @author a.navarro
 */
abstract class LughCipher extends Builder{
    
    protected $mailer;
    
    
    public function __construct($container) {
        parent::__construct($container);
        $this->mailer = $this->get('mailer.builder');
    }
    
    abstract function encode($votacion, $tipo);
    abstract function decode($votoCrypt, $tipo);
}

?>
