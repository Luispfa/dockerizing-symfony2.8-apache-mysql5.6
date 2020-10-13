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
class StateBuilderProd extends StateBuilder {
    
    public function __construct($container) {
        parent::__construct($container);
        $this->newState                     = new NewStateClass($container);
        $this->pendienteState               = new PendienteStateClass($container);
        $this->publicaState                 = new PublicaStateClass($container);
        $this->rechazadoState               = new RechazadoStateClass($container);
        $this->retornadoState               = new RetornadoStateClass($container);
        $this->lockedState                  = new LockedStateClass($container);
        $this->unlockedState                = new UnlockedStateClass($container);
        $this->enableState                  = new EnableStateClass($container);
        $this->disableState                 = new DisableStateClass($container);
        $this->delegacionPendienteState     = new DelegacionPendienteStateClass($container);
        $this->delegacionPublicaState       = new DelegacionPublicaStateClass($container);
        $this->delegacionRechazadoState     = new DelegacionRechazaStateClass($container);
        $this->configuracionState           = new ConfiguracionStateClass($container);
        $this->convocatoriaState            = new ConvocatoriaStateClass($container);
        $this->prejuntaState                = new PrejuntaStateClass($container);
        $this->asistenciaState              = new AsistenciaStateClass($container);
        $this->quorumCerradoState           = new QuorumCerradoStateClass($container);
        $this->votacionState                = new VotacionStateClass($container);
        $this->finalizadoState              = new FinalizadoStateClass($container);
    }
}

?>
