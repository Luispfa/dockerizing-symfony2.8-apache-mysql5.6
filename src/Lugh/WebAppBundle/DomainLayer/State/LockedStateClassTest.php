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
class LockedStateClassTest extends StateClass{
    
    public function pendiente($item, $comments = null) {
        return $item;
    }
    public function publica($item, $comments = null) {
        return $item;
    }
    public function retorna($item, $comments = null) {
        return $item;
    }
    public function rechaza($item, $comments = null) {
        return $item;
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
        $this->exceptionUser();
        $this->exceptionTime($item, $state);
    }
    
    private function exceptionUser()
    {
        if (!RestrictionsTest::hasUserPermitedChangeState())
        {
            throw new Exception("User not has permited change state");
        }
    }
    private function exceptionTime($item, $state)
    {
        if (!RestrictionsTest::inTime($item, $state))
        {
            throw new Exception("item not in time to change state " . $state);
        }
    }
}

?>
