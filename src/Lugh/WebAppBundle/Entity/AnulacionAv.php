<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * Anulacion
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class AnulacionAv extends Accion{
    
    const nameClass = 'AnulacionAv';
    const appClass  = 'Av';
    
    
}