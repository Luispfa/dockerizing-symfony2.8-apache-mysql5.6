<?php

namespace Lugh\WebAppBundle\DomainLayer;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parameters
 *
 * @author a.navarro
 */
class Mails extends LughService {
    
    
    public function getByKey($key, $default = null)
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT p FROM Lugh\WebAppBundle\Entity\Mails p WHERE p.key_param = :key');
        $query->setParameter('key', $key);
        
        $record = $query->getOneOrNullResult();
        
        if ($record == null)
        {
            return $default;
        }
        return $record->getValueParam();
    }
}

?>
