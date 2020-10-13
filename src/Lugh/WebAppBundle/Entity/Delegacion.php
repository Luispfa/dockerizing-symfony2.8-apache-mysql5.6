<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
/**
 * Delegacion
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Delegacion extends Accion{
    const nameClass = 'Delegacion';
    const appClass  = 'Voto';
    
    protected $delegationState;
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Delegado", inversedBy="delegacion", cascade={"persist", "remove"})
     * @Expose
     */
    private $delegado;    

    /**
     * @var boolean
     *
     * @ORM\Column(name="sustitucion", type="boolean", options={"default":true})
     * @Expose
     */
    private $sustitucion;

    /**
     * @var text
     *
     * @ORM\Column(name="observaciones", type="text")
     * @Expose
     */
    private $observaciones;
    
    /**
     * @ORM\Column(type="integer", options={"default":2})
     * @Expose
     */
    private $state = 2;
    
    public function __construct() {
        $this->setDelegationState($this->getState());
    }
    
    
    protected function setDelegationState($state)
    {
        if ($state == null)
        {
            $this->delegationState = $this->getContainer()->get('lugh.server')->getState()->getDelegacionPendienteState();
        }
        switch ($this->getState()) {
            case StateClass::statePending:
                $this->delegationState = $this->getContainer()->get('lugh.server')->getState()->getDelegacionPendienteState();
                break;
            case StateClass::statePublic:
                $this->delegationState = $this->getContainer()->get('lugh.server')->getState()->getDelegacionPublicaState();
                break;
            case StateClass::stateReject:
                $this->delegationState = $this->getContainer()->get('lugh.server')->getState()->getDelegacionRechazadoState();
                break;
            default:
                break;
        }
    }
    
    
    /** 
     * @ORM\PrePersist
     */
    public function doActionsOnPrePersist() {
        $this->restrictions($this);
    }
    
    public function preSave() {
        $item = $this->getAccionAnterior();
        
        if ($this->getId() == null)
        {
            $this->setDateTimeCreate(new \DateTime());
            $this->actionState($this);
        }
        
        if ($item != null && $item::appClass == 'Av')
        {
            throw new Exception("Voiting can't save after Av");
        }
    }
    
    /**
     * Add votacion
     *
     * @param \Lugh\WebAppBundle\Entity\VotoPunto $votacion
     * @return Accion
     */
    public function addVotacion(\Lugh\WebAppBundle\Entity\VotoPunto $votacion)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->delegationNoVoteInTime();
        parent::addVotacion($votacion);
        return $this;
    }
    
    private function restrictions($item)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->delegationNoDelegado($item);
        return $item;    
    }

    private function delegadoRestrictions($item)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->maxDelegation($item);
        return $item;    
    }
    
    private function actionState($delegation)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->delegationCreate($delegation);
        return $delegation;
    }
    
    
    public function setDelegadoNull()
    {
        $this->delegado = null;
    }
    
    /**
     * Set delegado
     *
     * @param \Lugh\WebAppBundle\Entity\Delegado $delegado
     * @return Delegacion
     */
    public function setDelegado(\Lugh\WebAppBundle\Entity\Delegado $delegado = null)
    {
        $this->delegado = $this->delegadoRestrictions($delegado);

        return $this;
    }

    /**
     * Get delegado
     *
     * @return \Lugh\WebAppBundle\Entity\Delegado 
     */
    public function getDelegado()
    {
        return $this->delegado;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return Delegacion
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
    
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setDelegationState($this->getState());
    }
    
    public function pendiente($comments = null) {
        return $this->delegationState->pendiente($this, $comments);
    }
    public function publica($comments = null) {
        return $this->delegationState->publica($this, $comments);
    }
    public function rechaza($comments = null) {
        return $this->delegationState->rechaza($this, $comments);
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Delegacion
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set sustitucion
     *
     * @param boolean $sustitucion
     * @return Delegacion
     */
    public function setSustitucion($sustitucion)
    {
        $this->sustitucion = $sustitucion;

        return $this;
    }

    /**
     * Get sustitucion
     *
     * @return boolean 
     */
    public function getSustitucion()
    {
        return $this->sustitucion;
    }
}
