<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;

/**
 * App
 *
 * @ORM\Entity  @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="integer")
 * @ORM\DiscriminatorMap({0 = "AppVoto", 1 = "AppForo", 2 = "AppDerecho", 3 = "AppAV"})
 * 
 * @ExclusionPolicy("all")
 */
abstract class App {
    
    const appVoto       = 0;
    const appForo       = 1;
    const appDerecho    = 2;
    const appAv         = 3;
    
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    
    protected $appState;
    
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
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="app", cascade={"persist"})
     * @ORM\JoinColumn(name="accionista_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $accionista;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="app", cascade={"persist", "remove"})
     * @Expose
     * @Type("array") 
     * @Accessor(getter="getMessages")
     * @ORM\OrderBy({"dateTime" = "DESC"})
     */
    private $messages;
    
    /** 
     * @ORM\PrePersist
     */
    public function doStateOnPrePersist()
    {
        //$this->preSave();
    }
    
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setAppState($this->getState());
    }
    
    
    protected function setAppState($state)
    {
        $this->setDateTime(new \DateTime());
        
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
            $this->appState = $lugh_state->getNewState();
        }
        switch ($this->getState()) {
            case StateClass::stateNew:
                $this->appState = $lugh_state->getNewState();
                break;
            case StateClass::statePending:
                $this->appState = $lugh_state->getPendienteState();
                break;
            case StateClass::statePublic:
                $this->appState = $lugh_state->getPublicaState();
                break;
            case StateClass::stateRetornate:
                $this->appState = $lugh_state->getRetornadoState();
                break;
            case StateClass::stateReject:
                $this->appState = $lugh_state->getRechazadoState();
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
    
    abstract function getAppClass();

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
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
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
        $this->setappState($state);

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
        $this->dateTime = $dateTime;

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
    
    /**
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return App
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista)
    {
        //$accionista->setApp($this); /* @TODO: Apps */
        $this->accionista = $accionista;

        return $this;
    }

    /**
     * Get accionista
     *
     * @return \Lugh\WebAppBundle\Entity\Accionista 
     */
    public function getAccionista()
    {
        return $this->accionista;
    }
    
    /**
     * Add messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     * @return ItemAccionista
     */
    public function addMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $messages->setApp($this);
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     */
    public function removeMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
