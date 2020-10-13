<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * Av
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class Av extends Accion{
    const nameClass = 'Av';
    const appClass  = 'Av';
    
    /** 
     * @ORM\PrePersist 
     */
    public function doVotoOnPrePersist()
    {
        if (
                (($this->getVotacion() != null &&
                $this->getVotacion()->count() < 1) ||
                $this->getVotacion() == null) &&
                (($this->getVotacionSerie() != null &&
                count($this->getVotacionSerie()) < 1) ||
                $this->getVotacionSerie() == null))
        {
            throw new Exception("Voiting should have one point as a minium");
        }
    }

}
