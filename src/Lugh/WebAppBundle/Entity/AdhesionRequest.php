<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * AdhesionRequest
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AdhesionRequest extends Adhesion
{
    const nameClass = 'AdhesionRequest';
    const appClass  = 'Foro';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Request", inversedBy="adhesions")
     * @ORM\JoinColumn(name="request_id", referencedColumnName="id", nullable=false)
     * @Expose
     * @Groups({"requests", "VarMail"})
     */
    private $request;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="adhesionsRequests")
     * @ORM\JoinColumn(name="accionista_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $accionista;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    public function __construct() {
        $this->setAdhesionState($this->getState());
    }
      
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setAdhesionState($this->getState());
    }
    public function pendiente($comments = null) {
        return $this->adhesionState->pendiente($this, $comments);
    }
    public function publica($comments = null) {
        return $this->adhesionState->publica($this, $comments);
    }
    public function retorna($comments = null) {
        return $this->adhesionState->retorna($this, $comments);
    }
    public function rechaza($comments = null) {
        return $this->adhesionState->rechaza($this, $comments);
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
     * Set request
     *
     * @param \Lugh\WebAppBundle\Entity\Request $request
     * @return AdhesionRequest
     */
    public function setRequest(\Lugh\WebAppBundle\Entity\Request $request = null)
    {
        
        $this->request = $this->restrictions($request, $this);
        return $this;
    }

    /**
     * Get request
     *
     * @return \Lugh\WebAppBundle\Entity\Request 
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Get request
     *
     * @return \Lugh\WebAppBundle\Entity\Request 
     */
    public function getItem()
    {
        return $this->request;
    }
    
    private function restrictions($item,$adhesion, $action= StateClass::actionGet)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->selfAdhesion($item, $adhesion);
        $behavior->multipleAdhesion($item, $adhesion);
        $behavior->hasUserPermission($item, $action);
        return $item;    
    }
    /**
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return AdhesionRequest
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista)
    {
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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return AdhesionRequest
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
}
