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
 * Junta
 *
 * @ORM\Entity  @ORM\HasLifecycleCallbacks
 * 
 * @ExclusionPolicy("all")
 */
class Junta {
    
    const nameClass = 'Junta';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    
    protected $juntaState;
    
    /**
     * @ORM\Column(type="integer" , options={"default":0})
     * @Expose
     */
    private $state;
    
    /**
     * @ORM\Column(name="acreditacion_enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $acreditacionEnabled;
    
    /**
     * @ORM\Column(name="votacion_enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $votacionEnabled;
    
    /**
     * @ORM\Column(name="preguntas_enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $preguntasEnabled;
    
    /**
     * @ORM\Column(name="live_enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $liveEnabled;
    
    /**
     * @ORM\Column(name="abandono_enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $abandonoEnabled;

    
    /** 
     * @ORM\PrePersist
     */
    public function doStateOnPrePersist()
    {
        $this->preSave();
    }
    
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setJuntaState($this->getState());
    }
    
    
    protected function setJuntaState($state)
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
            $this->juntaState = $lugh_state->getNewState();
        }
        switch ($this->getState()) {
            case StateClass::stateConfiguracion:
                $this->juntaState = $lugh_state->getConfiguracionState();
                break;
            case StateClass::stateConvocatoria:
                $this->juntaState = $lugh_state->getConvocatoriaState();
                break;
            case StateClass::statePrejunta:
                $this->juntaState = $lugh_state->getPrejuntaState();
                break;
            case StateClass::stateAsistencia:
                $this->juntaState = $lugh_state->getAsistenciaState();
                break;
            case StateClass::stateQuorumCerrado:
                $this->juntaState = $lugh_state->getQuorumCerradoState();
                break;
            case StateClass::stateVotacion:
                $this->juntaState = $lugh_state->getVotacionState();
                break;
            case StateClass::stateFinalizado:
                $this->juntaState = $lugh_state->getFinalizadoState();
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

    public function __construct() {
        $this->setJuntaState($this->getState());
    }
        
    public function configuracion() {
        return $this->juntaState->configuracion($this);
    }
    public function convocatoria() {
        return $this->juntaState->convocatoria($this);
    }
    public function prejunta() {
        return $this->juntaState->prejunta($this);
    }
    public function asistencia() {
        return $this->juntaState->asistencia($this);
    }
    public function quorumcerrado() {
        return $this->juntaState->quorumcerrado($this);
    }
    public function votacion() {
        return $this->juntaState->votacion($this);
    }
    public function finalizado() {
        return $this->juntaState->finalizado($this);
    }


    public function preSave()
    {
         $storage = $this->getContainer()->get('lugh.server')->getStorage();
        if ($this->getId() == null)
        {
            if ($storage->getJuntas() !== false)
            {
                throw new Exception('Operation new Junta not permited');
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
     * @return Junta
     */
    public function setState($state)
    {
        $this->state = $state;
        $this->setJuntaState($state);

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
     * Set acreditacionEnabled
     *
     * @param boolean $acreditacionEnabled
     * @return Junta
     */
    public function setAcreditacionEnabled($acreditacionEnabled)
    {
        $this->acreditacionEnabled = $acreditacionEnabled;

        return $this;
    }

    /**
     * Get acreditacionEnabled
     *
     * @return boolean 
     */
    public function getAcreditacionEnabled()
    {
        return $this->acreditacionEnabled;
    }

    /**
     * Set votacionEnabled
     *
     * @param boolean $votacionEnabled
     * @return Junta
     */
    public function setVotacionEnabled($votacionEnabled)
    {
        $this->votacionEnabled = $votacionEnabled;

        return $this;
    }

    /**
     * Get votacionEnabled
     *
     * @return boolean 
     */
    public function getVotacionEnabled()
    {
        return $this->votacionEnabled;
    }

    /**
     * Set preguntasEnabled
     *
     * @param boolean $preguntasEnabled
     * @return Junta
     */
    public function setPreguntasEnabled($preguntasEnabled)
    {
        $this->preguntasEnabled = $preguntasEnabled;

        return $this;
    }

    /**
     * Get preguntasEnabled
     *
     * @return boolean 
     */
    public function getPreguntasEnabled()
    {
        return $this->preguntasEnabled;
    }

    /**
     * Set liveEnabled
     *
     * @param boolean $liveEnabled
     * @return Junta
     */
    public function setLiveEnabled($liveEnabled)
    {
        $this->liveEnabled = $liveEnabled;

        return $this;
    }

    /**
     * Get liveEnabled
     *
     * @return boolean 
     */
    public function getLiveEnabled()
    {
        return $this->liveEnabled;
    }
    
    /**
     * Set abndonoEnabled
     *
     * @param boolean $abandonoEnabled
     * @return Junta
     */
    public function setAbandonoEnabled($abandonoEnabled)
    {
        $this->abandonoEnabled = $abandonoEnabled;

        return $this;
    }

    /**
     * Get abandonoEnabled
     *
     * @return boolean 
     */
    public function getAbandonoEnabled()
    {
        return $this->abandonoEnabled;
    }
}
