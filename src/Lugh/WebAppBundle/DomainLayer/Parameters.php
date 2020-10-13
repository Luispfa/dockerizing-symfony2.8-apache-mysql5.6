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
class Parameters extends LughService {
    
    
    public function getByKey($key, $default = null)
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT p FROM Lugh\WebAppBundle\Entity\Parametros p WHERE p.key_param = :key');
        $query->setParameter('key', $key);
        
        $record = $query->getOneOrNullResult();
        
        if ($record == null)
        {
            return $default;
        }
        return $record->getValueParam();
    }
    
    public function getParams()
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery("SELECT p FROM Lugh\WebAppBundle\Entity\Parametros p WHERE "
                . "(p.key_param LIKE 'Config.%' "
                . "or p.key_param LIKE '%.time.activate' or p.key_param LIKE '%.live.address') "
                . "and p.key_param not LIKE '%.mail.%'");
        return $query->getArrayResult();
    }
    
    public function getRequireParms()
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery("SELECT p FROM Lugh\WebAppBundle\Entity\Parametros p WHERE p.key_param LIKE 'Config.require.%'");
        return $query->getArrayResult();
    }
    
    public function getVotoParam($default = null)
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery("SELECT p FROM Lugh\WebAppBundle\Entity\Parametros p WHERE p.key_param LIKE 'Voto.pieces.config'");
        $record = $query->getOneOrNullResult();
        
        if ($record == null)
        {
            return $default;
        }
        return $record->getValueParam();
    }
    
    public function getAvVotoParam($default = null)
    {
        $em = $this->get('doctrine')->getManager();
        $query = $em->createQuery("SELECT p FROM Lugh\WebAppBundle\Entity\Parametros p WHERE p.key_param LIKE 'AvVoto.pieces.config'");
        $record = $query->getOneOrNullResult();
        
        if ($record == null)
        {
            return $default;
        }
        return $record->getValueParam();
    }
}

?>
