<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * Anulacion
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class Anulacion extends Accion{
    
    const nameClass = 'Anulacion';
    const appClass  = 'Voto';
    
    /** 
     * @ORM\PrePersist
     */
    public function doActionsOnPrePersist() {
        $this->preSave();
    }
    
    public function preSave()
    {
        $item = $this->getAccionAnterior();
        if ($item != null && $item::appClass == 'Av')
        {
            throw new Exception("Voiting can't save after Av");
        }
        if ($this->getId() == null)
        {
            $this->setDateTimeCreate(new \DateTime());
        }
    }
    
    
}