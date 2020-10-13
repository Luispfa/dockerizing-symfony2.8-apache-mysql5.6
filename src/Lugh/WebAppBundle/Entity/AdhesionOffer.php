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
 * AdhesionOffer
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AdhesionOffer extends Adhesion
{
    const nameClass = 'AdhesionOffer';
    const appClass  = 'Foro';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="adhesions")
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id", nullable=false)
     * @Expose
     * @Groups({"offers", "VarMail"}) 
     */
    private $offer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="adhesionsOffers")
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
     * Set offer
     *
     * @param \Lugh\WebAppBundle\Entity\Offer $offer
     * @return AdhesionOffer
     */
    public function setOffer(\Lugh\WebAppBundle\Entity\Offer $offer = null)
    {
        $this->offer = $this->restrictions($offer, $this);
        return $this;
    }

    /**
     * Get offer
     *
     * @return \Lugh\WebAppBundle\Entity\Offer 
     */
    public function getOffer()
    {
        return $this->offer;
    }
    
    /**
     * Get offer
     *
     * @return \Lugh\WebAppBundle\Entity\Offer 
     */
    public function getItem()
    {
        return $this->offer;
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
     * @return AdhesionOffer
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
     * @return AdhesionOffer
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
