<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StateBuilder
 *
 * @author a.navarro
 */
abstract class StateBuilder extends Builder{
    
    protected $newState;
    
    protected $pendienteState;
    
    protected $publicaState;
    
    protected $rechazadoState;
    
    protected $retornadoState;
    
    protected $lockedState;
    
    protected $unlockedState;
    
    protected $enableState;
    
    protected $disableState;
    
    protected $delegacionPendienteState;
    
    protected $delegacionPublicaState;
    
    protected $delegacionRechazadoState;
    
    protected $configuracionState;
    
    protected $convocatoriaState;
    
    protected $prejuntaState;
    
    protected $asistenciaState;
    
    protected $quorumCerradoState;
    
    protected $votacionState;
    
    protected $finalizadoState;
    
    public function getNewState()
    {
        return $this->newState;
    }
    
    public function getPendienteState()
    {
        return $this->pendienteState;
    }
    
    
    public function getPublicaState()
    {
        return $this->publicaState;
    }
    
    
    public function getRechazadoState()
    {
        return $this->rechazadoState;
    }
    
    
    public function getRetornadoState()
    {
        return $this->retornadoState;
    }
    
    public function getLockedState()
    {
        return $this->lockedState;
    }
    
    
    public function getUnlockedState()
    {
        return $this->unlockedState;
    }
    
    public function getEnableState()
    {
        return $this->enableState;
    }
    
    public function getDisableState()
    {
        return $this->disableState;
    }
    
    public function getDelegacionPendienteState()
    {
        return $this->delegacionPendienteState;
    }
    
    
    public function getDelegacionPublicaState()
    {
        return $this->delegacionPublicaState;
    }
    
    
    public function getDelegacionRechazadoState()
    {
        return $this->delegacionRechazadoState;
    }
    
    public function getConfiguracionState()
    {
        return $this->configuracionState;
    }
    
    public function getConvocatoriaState()
    {
        return $this->convocatoriaState;
    }
    
    public function getPrejuntaState()
    {
        return $this->prejuntaState;
    }
    
    public function getAsistenciaState()
    {
        return $this->asistenciaState;
    }
    
    public function getQuorumCerradoState()
    {
        return $this->quorumCerradoState;
    }
    
    public function getVotacionState()
    {
        return $this->votacionState;
    }
    
    public function getFinalizadoState()
    {
        return $this->finalizadoState;
    }
    
}

?>
