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
 * AccesoVoto
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AccesoVoto extends Acceso
{
    const nameClass = 'AccesoVoto';

    public function __construct() {
        parent::__construct();
    }
    
    public function getAccesoClass() {
        return 'Voto';
    }
    
    public function findAccesoAnterior()
    {
        if (is_array($this->getAccionista()->getAllAccesoForFind('voto')))
        {
            $accesos = $this->getAccionista()->getAllAccesoForFind('voto');
            return end($accesos);
        }
        return $this->getAccionista()->getAllAccesoForFind('voto')->last();
    }
}
