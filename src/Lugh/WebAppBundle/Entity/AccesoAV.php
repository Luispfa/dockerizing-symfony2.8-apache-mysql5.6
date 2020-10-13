<?php

namespace Lugh\WebAppBundle\Entity;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccesoAV
 *
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class AccesoAV extends Acceso
{
    const nameClass = 'AccesoAV';
    
    public function __construct() {
        parent::__construct();
    }
        
    public function getAccesoClass() {
        return 'AV';
    }
    
    public function findAccesoAnterior()
    {
        if (is_array($this->getAccionista()->getAllAccesoForFind('av')))
        {
            $accesos = $this->getAccionista()->getAllAccesoForFind('av');
            return end($accesos);
        }
        return $this->getAccionista()->getAllAccesoForFind('av')->last();
    }
}
