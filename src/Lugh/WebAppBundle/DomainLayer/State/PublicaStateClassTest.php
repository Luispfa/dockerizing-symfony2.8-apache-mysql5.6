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
class PublicaStateClassTest extends PublicaStateClass {
    
    public function pendiente($item, $comments = null) {
        $this->selfState($item, self::statePending);
        $this->restrictions($item, self::actionPending);
        $this->AccionistaChangeStatePending($item);
        return $this->ItemChangeStatePending($item);
    }
    public function publica($item, $comments = null) {
        $this->selfState($item, self::statePublic);
        $this->restrictions($item, self::actionPublic);
        $this->AccionistaChangeStatePublic($item);
        return $this->ItemChangeStatePublic($item);
    }
    public function retorna($item, $comments = null) {
        $this->selfState($item, self::stateRetornate);
        $this->restrictions($item, self::actionRetornate);
        $this->AccionistaChangeStateRetornate($item);
        return $this->ItemChangeStateRetornate($item);
    }
    public function rechaza($item, $comments = null) {
        $this->selfState($item, self::stateReject);
        $this->restrictions($item, self::actionReject);
        $this->AccionistaChangeStateReject($item);
        return $this->ItemChangeStateReject($item);
    }
    
    public function locked($item, $comments = null) {
        return true;
    }

    public function unlocked($item, $comments = null) {
        return true;
    }
    
    public function enable($item, $comments = null) {
        return true;
    }

    public function disable($item, $comments = null) {
        return true;
    }
    
    public function asistencia($junta) {
        return $junta;
    }

    public function configuracion($junta) {
        return $junta;
    }

    public function convocatoria($junta) {
        return $junta;
    }

    public function finalizado($junta) {
        return $junta;
    }

    public function prejunta($junta) {
        return $junta;
    }

    public function quorumcerrado($junta) {
        return $junta;
    }

    public function votacion($junta) {
        return $junta;
    }
}

?>
