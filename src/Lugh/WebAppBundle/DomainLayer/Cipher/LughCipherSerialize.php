<?php
namespace Lugh\WebAppBundle\DomainLayer\Cipher;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughCipherTest
 *
 * @author a.navarro
 */
class LughCipherSerialize extends LughCipher{
    
    public function decode($votoCrypt, $tipo) {
        return unserialize($votoCrypt);
    }

    public function encode($votacion, $tipo) {
        return serialize($votacion);
    }

}

?>