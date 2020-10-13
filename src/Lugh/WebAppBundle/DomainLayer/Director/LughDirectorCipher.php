<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughDirectorBuilder
 *
 * @author a.navarro
 */
class LughDirectorCipher extends LughDirector {
    
    public function decode($votoCrypt, $tipo) {
        return $this->builder->decode($votoCrypt, $tipo);
    }
    
    public function encode($votacion, $tipo) {
        return $this->builder->encode($votacion, $tipo);
    }
}

?>
