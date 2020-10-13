<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * AccionRechazada
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class AccionRechazada extends Accion{
    
    const nameClass = 'AccionRechazada';
    const appClass  = 'Voto';
    
}
