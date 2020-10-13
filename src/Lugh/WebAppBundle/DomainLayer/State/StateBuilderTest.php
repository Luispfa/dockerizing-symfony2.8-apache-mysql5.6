<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StateBuilder
 *
 * @author a.navarro
 */
class StateBuilderTest extends StateBuilder {
    
    public function __construct($container) {
        parent::__construct($container);
        $this->newState                     = new NewStateClassTest($container);
        $this->pendienteState               = new PendienteStateClassTest($container);
        $this->publicaState                 = new PublicaStateClassTest($container);
        $this->rechazadoState               = new RechazadoStateClassTest($container);
        $this->retornadoState               = new RetornadoStateClassTest($container);
        $this->lockedState                  = new LockedStateClassTest($container);
        $this->unlockedState                = new UnlockedStateClassTest($container);
        $this->enableState                  = new EnableStateClassTest($container);
        $this->disableState                 = new DisableStateClassTest($container);
        $this->delegacionPendienteState     = new DelegacionPendienteStateClass($container);
        $this->delegacionPublicaState       = new DelegacionPublicaStateClass($container);
        $this->delegacionRechazadoState     = new DelegacionRechazaStateClass($container);
        $this->convocatoriaState            = new ConvocatoriaStateClass($container);
        $this->prejuntaState                = new PrejuntaStateClass($container);
        $this->asistenciaState              = new AsistenciaStateClass($container);
        $this->quorumCerradoState           = new QuorumCerradoStateClass($container);
        $this->votacionState                = new VotacionStateClass($container);
        $this->finalizadoState              = new FinalizadoStateClass($container);
    }
}

?>
