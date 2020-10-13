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
 * AccesoDerecho
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AccesoDerecho extends Acceso
{
    const nameClass = 'AccesoDerecho';

    public function __construct() {
        parent::__construct();
    }
        
    public function getAccesoClass() {
        return 'Derecho';
    }
    
    public function findAccesoAnterior()
    {
        if (is_array($this->getAccionista()->getAllAccesoForFind('derecho')))
        {
            $accesos = $this->getAccionista()->getAllAccesoForFind('derecho');
            return end($accesos);
        }
        return $this->getAccionista()->getAllAccesoForFind('derecho')->last();
    }
}
