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
 * AccesoForo
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AccesoForo extends Acceso
{
    const nameClass = 'AccesoForo';

    public function __construct() {
        parent::__construct();
    }
        
    public function getAccesoClass() {
        return 'Foro';
    }
    
    public function findAccesoAnterior()
    {
        if (is_array($this->getAccionista()->getAllAccesoForFind('foro')))
        {
            $accesos = $this->getAccionista()->getAllAccesoForFind('foro');
            return end($accesos);
        }
        return $this->getAccionista()->getAllAccesoForFind('foro')->last();
    }
}
