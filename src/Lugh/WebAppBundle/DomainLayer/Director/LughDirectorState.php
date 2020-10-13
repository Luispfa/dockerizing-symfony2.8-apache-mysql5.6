<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughDirectorBuilder
 *
 * @author a.navarro
 */
class LughDirectorState extends LughDirector {
    
    public function getPendienteState() {
        return $this->builder->getPendienteState();
    }
    
    public function getNewState()
    {
        return $this->builder->getNewState();
    }
    
    public function getPublicaState()
    {
        return $this->builder->getPublicaState();
    }
    
    
    public function getRechazadoState()
    {
        return $this->builder->getRechazadoState();
    }
    
    
    public function getRetornadoState()
    {
        return $this->builder->getRetornadoState();
    }
    
    public function getLockedState()
    {
        return $this->builder->getLockedState();
    }
    
    public function getUnlockedState()
    {
        return $this->builder->getUnlockedState();
    }
    
    public function getEnableState()
    {
        return $this->builder->getEnableState();
    }
    
    public function getDisableState()
    {
        return $this->builder->getDisableState();
    }
    
    public function getDelegacionPendienteState()
    {
        return $this->builder->getDelegacionPendienteState();
    }
    
    public function getDelegacionPublicaState()
    {
        return $this->builder->getDelegacionPublicaState();
    }
    
    public function getDelegacionRechazadoState()
    {
        return $this->builder->getDelegacionRechazadoState();
    }
    
    public function getConfiguracionState()
    {
        return $this->builder->getConfiguracionState();
    }
    
    public function getConvocatoriaState()
    {
        return $this->builder->getConvocatoriaState();
    }
    
    public function getPrejuntaState()
    {
        return $this->builder->getPrejuntaState();
    }
    
    public function getAsistenciaState()
    {
        return $this->builder->getAsistenciaState();
    }
    
    public function getQuorumCerradoState()
    {
        return $this->builder->getQuorumCerradoState();
    }
    
    public function getVotacionState()
    {
        return $this->builder->getVotacionState();
    }
    
    public function getFinalizadoState()
    {
        return $this->builder->getFinalizadoState();
    }
}

?>
