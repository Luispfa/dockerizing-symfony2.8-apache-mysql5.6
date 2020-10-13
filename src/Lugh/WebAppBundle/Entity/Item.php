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
 * Item
 *
 * @ORM\MappedSuperclass
 * 
 * @ExclusionPolicy("all")
 */
abstract class Item {
    

    protected $itemState;
    
    /**
     * @ORM\Column(type="integer" , options={"default":0})
     * @Expose
     */
    private $state;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    
    protected function setitemState($state)
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
            $this->itemState = $lugh_state->getNewState();
        }
        switch ($this->getState()) {
            case StateClass::stateNew:
                $this->itemState = $lugh_state->getNewState();
                break;
            case StateClass::statePending:
                $this->itemState = $lugh_state->getPendienteState();
                break;
            case StateClass::statePublic:
                $this->itemState = $lugh_state->getPublicaState();
                break;
            case StateClass::stateRetornate:
                $this->itemState = $lugh_state->getRetornadoState();
                break;
            case StateClass::stateReject:
                $this->itemState = $lugh_state->getRechazadoState();
                break;
            default:
                break;
        }
    }
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }

    abstract function pendiente($comments = null);
    abstract function publica($comments = null);
    abstract function retorna($comments = null);
    abstract function rechaza($comments = null);
    
    /*abstract function setAutor(\Lugh\WebAppBundle\Entity\Accionista $autor = null);
    abstract function getAutor();*/
    
    /*abstract function addAdhesion(\Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesions);
    abstract function removeAdhesion(\Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesions);
    abstract function getAdhesions();*/

    public function preSave()
    {
        if ($this->getId() == null)
        {

            $behavior = $this->getContainer()->get('lugh.server')->getBehavior();

            $state = $behavior->getDefaultState($this);
            
            $comments = count($this->getMessages()) == 1 ? $this->getMessages()[0]->getBody() : null;
            switch ($state) {
                case StateClass::statePending:
                    $this->pendiente($comments);
                    break;
                case StateClass::statePublic:
                    $this->publica($comments);
                    break;
                case StateClass::stateRetornate:
                    $this->retorna($comments);
                    break;
                case StateClass::stateReject:
                    $this->rechaza($comments);
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
     * @return Item
     */
    public function setState($state)
    {
        $this->state = $state;
        $this->setitemState($state);

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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Item
     */
    public function setDateTime($dateTime)
    {
        if($this->dateTime == null){
            $this->dateTime = $dateTime;
        }
        
        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime 
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
