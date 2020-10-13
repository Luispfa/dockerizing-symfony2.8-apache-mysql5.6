<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * Voto
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class Voto extends Accion{
    const nameClass = 'Voto';
    const appClass  = 'Voto';
    
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
    
    public function preSave()
    {
        $item = $this->getAccionAnterior();
        if ($item != null && $item::appClass == 'Av')
        {
            throw new Exception("Voiting can't save after Av");
        }
    }

}
