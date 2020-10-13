<?php
namespace Lugh\WebAppBundle\DomainLayer\Cipher;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughCipherProd
 *
 * @author a.navarro
 */
class LughCipherJson extends LughCipher{

    public function decode($votacion, $tipo) {
        return json_decode($votacion, true);
    }

    public function encode($votacion, $tipo) {
        return json_encode($votacion);
    }

}

?>
