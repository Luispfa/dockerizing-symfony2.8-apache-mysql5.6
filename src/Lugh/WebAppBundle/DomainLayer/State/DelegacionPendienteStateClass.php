<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
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
class DelegacionPendienteStateClass extends StateClass {
    
    public function pendiente($item, $comments = null) {
        //$this->selfState($item, self::statePending);
        //$this->restrictions($item, self::actionPending);
        //$this->mailerState($item,self::actionPending);
        //return $item->setState(self::statePending);
        return $item;
    }
    public function publica($item, $comments = null) {
        $this->lastItem($item);
        $this->selfState($item, self::statePublic);
        $this->restrictions($item, self::actionPublic);
        $this->mailerState($item,self::actionPublic, $this->getExternal($comments));
        return $item->setState(self::statePublic);
    }
    public function retorna($item, $comments = null) {
        return $item;
    }
    public function rechaza($item, $comments = null) {
        $this->lastItem($item);
        $this->selfState($item, self::stateReject);
        $this->restrictions($item, self::actionReject);
        $this->mailerState($item,self::actionReject, $this->getExternal($comments));
        return $item->setState(self::stateReject);
    }
    
    public function locked($item, $comments = null) {
        return $item;
    }

    public function unlocked($item, $comments = null) {
        return $item;
    }
    
    public function enable($item, $comments = null) {
        return $item;
    }

    public function disable($item, $comments = null) {
        return $item;
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
    
    private function restrictions($item, $state)
    {
        //$this->exceptionUser($item, $state);
        $this->exceptionTime($item, $state);
    }
    
    private function exceptionUser($item, $state)
    {
        if (!Restrictions::hasUserPermitedChangeState($item, $state))
        {
            throw new Exception("User not has permited change state");
        }
    }
    private function exceptionTime($item, $state)
    {
        if (!Restrictions::inTime($item, $state))
        {
            throw new Exception("item not in time to change state " . $state);
        }
    }
    private function selfState($item, $state)
    {
        if (Restrictions::selfState($item, $state))
        {
            throw new Exception("No change to self state");
        }
    }
    private function lastItem($item)
    {
        if (!Restrictions::lastItem($item))
        {
            throw new Exception("No last Item");
        }
    }

}

?>
