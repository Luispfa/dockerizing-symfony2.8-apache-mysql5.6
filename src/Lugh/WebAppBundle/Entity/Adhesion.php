<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Lugh\WebAppBundle\DomainLayer\State\PendienteStateClass;
use Lugh\WebAppBundle\DomainLayer\State\PublicaStateClass;
use Lugh\WebAppBundle\DomainLayer\State\RetornadoStateClass;
use Lugh\WebAppBundle\DomainLayer\State\RechazadoStateClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Adhesion
 *
 * @ORM\MappedSuperclass 
 * @ExclusionPolicy("all")
 */
abstract class Adhesion  {
    

    protected $adhesionState;
    
     /**
     * @ORM\Column(type="integer", options={"default":0})
     * @Expose
     */
    private $state;
    
    public function __construct() {
        $this->setState(StateClass::stateNew);
    }
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    protected function setAdhesionState($state)
    {
        switch ($this->getContainer()->get('lugh.mode')->getMode()) {
            case 'prod':
                $lugh_state = $this->getContainer()->get('lugh.server')->getState();
                break;
            case 'test':
                $lugh_state = $this->getContainer()->get('lugh.server')->getStateTest();
                break;
            default:
                $lugh_state = $this->getContainer()->get('lugh.server')->getState();
                break;
        }
        
        if ($state == null)
        {
            $this->adhesionState = $lugh_state->getNewState();
        }
        switch ($this->getState()) {
            case StateClass::stateNew:
                $this->adhesionState = $lugh_state->getNewState();
                break;
            case StateClass::statePending:
                $this->adhesionState = $lugh_state->getPendienteState();
                break;
            case StateClass::statePublic:
                $this->adhesionState = $lugh_state->getPublicaState();
                break;
            case StateClass::stateRetornate:
                $this->adhesionState = $lugh_state->getRetornadoState();
                break;
            case StateClass::stateReject:
                $this->adhesionState = $lugh_state->getRechazadoState();
                break;
            default:
                break;
        }
    }

    abstract function pendiente($comments = null);
    abstract function publica($comments = null);
    abstract function retorna($comments = null);
    abstract function rechaza($comments = null);
    
    
    public function preSave()
    {
        if ($this->getId() == null)
        {
            $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
            $state = $behavior->getDefaultState($this);
            switch ($state) {
                case StateClass::statePending:
                    $this->pendiente();
                    break;
                case StateClass::statePublic:
                    $this->publica();
                    break;
                case StateClass::stateRetornate:
                    $this->retorna();
                    break;
                case StateClass::stateReject:
                    $this->rechaza();
                    break;
                default:
                    break;
            }
        }
    }
    
    /**
     * Set state
     *
     * @param integer $state
     * @return Adhesion
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }
}
