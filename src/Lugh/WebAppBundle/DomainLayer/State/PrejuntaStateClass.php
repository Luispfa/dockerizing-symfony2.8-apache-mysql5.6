<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
use Lugh\WebAppBundle\DomainLayer\State\RestrictionsTest;
use Symfony\Component\Config\Definition\Exception\Exception;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accionista
 *
 * @author a.navarro
 */
class PrejuntaStateClass extends JuntaStateClass {
    
    public function asistencia($junta) {
        $junta->setState(StateClass::stateAsistencia);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateAsistencia) == null) {
            parent::asistencia($junta);
        }
        return $junta;
    }

    public function configuracion($junta) {
        $junta->setState(StateClass::stateConfiguracion);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateConfiguracion) == null) {
            parent::configuracion($junta);
        }
        return $junta;
    }

    public function convocatoria($junta) {
        $junta->setState(StateClass::stateConvocatoria);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateConvocatoria) == null) {
            parent::convocatoria($junta);
        }
        return $junta;
    }

    public function finalizado($junta) {
        $junta->setState(StateClass::stateFinalizado);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateFinalizado) == null) {
            parent::finalizado($junta);
        }
        return $junta;
    }

    public function prejunta($junta) {
        $junta->setState(StateClass::statePrejunta);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::statePrejunta) == null) {
            parent::prejunta($junta);
        }
        return $junta;
    }

    public function quorumcerrado($junta) {
        $junta->setState(StateClass::stateQuorumCerrado);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateQuorumCerrado) == null) {
            parent::quorumcerrado($junta);
        }
        return $junta;
    }

    public function votacion($junta) {
        $junta->setState(StateClass::stateVotacion);
        if ($this->setStatesDefault($junta, StateClass::statePrejunta, StateClass::stateVotacion) == null) {
            parent::votacion($junta);
        }
        return $junta;
    }
    
    protected function setStatesDefault($junta, $state, $toState) {
        $behavior = $this->get('lugh.server')->getBehavior();
        return $behavior->getJuntaStateEnabled($junta, $state, $toState);
    }
}

?>
