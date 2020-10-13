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
class JuntaStateClass extends StateClass {
    
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
        $this->setStateEnabled($junta, 'Acreditacion', true);
        $this->setStateEnabled($junta, 'Preguntas', true);
        $this->setStateEnabled($junta, 'Live', true);
        $this->setStateEnabled($junta, 'Votacion', true);
        $this->setStateEnabled($junta, 'Abandono', true);
        return $junta;
    }

    public function configuracion($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', false);
        $this->setStateEnabled($junta, 'Live', false);
        $this->setStateEnabled($junta, 'Votacion', false);
        $this->setStateEnabled($junta, 'Abandono', false);
        return $junta;
    }

    public function convocatoria($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', false);
        $this->setStateEnabled($junta, 'Live', false);
        $this->setStateEnabled($junta, 'Votacion', false);
        $this->setStateEnabled($junta, 'Abandono', false);
        return $junta;
    }

    public function finalizado($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', false);
        $this->setStateEnabled($junta, 'Live', false);
        $this->setStateEnabled($junta, 'Votacion', false);
        $this->setStateEnabled($junta, 'Abandono', false);
        return $junta;
    }

    public function prejunta($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', false);
        $this->setStateEnabled($junta, 'Live', false);
        $this->setStateEnabled($junta, 'Votacion', false);
        $this->setStateEnabled($junta, 'Abandono', false);
        return $junta;
    }

    public function quorumcerrado($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', true);
        $this->setStateEnabled($junta, 'Live', true);
        $this->setStateEnabled($junta, 'Abandono', true);
        $this->setStateEnabled($junta, 'Votacion', true);
        return $junta;
    }

    public function votacion($junta) {
        $this->setStateEnabled($junta, 'Acreditacion', false);
        $this->setStateEnabled($junta, 'Preguntas', false);
        $this->setStateEnabled($junta, 'Votacion', true);
        $this->setStateEnabled($junta, 'Live', true);
        $this->setStateEnabled($junta, 'Abandono', true);
        return $junta;
    }
    
    protected function setStateEnabled($junta, $state, $enabled)
    {
        $behavior = $this->get('lugh.server')->getBehavior();
        return $behavior->setJuntaStateEnabled($junta, $state, $enabled);
    }
}

?>
