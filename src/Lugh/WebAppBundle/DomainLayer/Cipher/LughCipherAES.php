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
class LughCipherAES extends LughCipher{

    public function decode($votacion, $tipo) {
        $cipher = $this->getClassCipher($tipo);
        return json_decode($cipher->decode($votacion), true);
    }

    public function encode($votacion, $tipo) {
        $cipher = $this->getClassCipher($tipo);
        return $cipher->encode(json_encode($votacion));
    }
    
    private function getClassCipher($tipo)
    {
        switch ($tipo) {
            case 'AESKey1':
                $cipher = new LughCipherAESKey1();
                break;
            case 'AESKey2':
                $cipher = new LughCipherAESKey2();
                break;
            default:
                $cipher = new LughCipherAESKey1();
                break;
        }
        return $cipher;
    }
    

}

?>
