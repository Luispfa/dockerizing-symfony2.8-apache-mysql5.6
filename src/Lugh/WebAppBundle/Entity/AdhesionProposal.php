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
 * AdhesionProposal
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AdhesionProposal extends Adhesion
{
    const nameClass = 'AdhesionProposal';
    const appClass  = 'Foro';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="adhesions")
     * @ORM\JoinColumn(name="proposal_id", referencedColumnName="id", nullable=false)
     * @Expose
     * @Groups({"proposals", "VarMail"}) 
     */
    private $proposal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="adhesionsProposals")
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
     * Set proposal
     *
     * @param \Lugh\WebAppBundle\Entity\Proposal $proposal
     * @return AdhesionProposal
     */
    public function setProposal(\Lugh\WebAppBundle\Entity\Proposal $proposal = null)
    {
     
        $this->proposal = $this->restrictions($proposal, $this);
        return $this;
    }

    /**
     * Get proposal
     *
     * @return \Lugh\WebAppBundle\Entity\Proposal 
     */
    public function getProposal()
    {
        return $this->proposal;
    }
    
    /**
     * Get proposal
     *
     * @return \Lugh\WebAppBundle\Entity\Proposal 
     */
    public function getItem()
    {
        return $this->proposal;
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
     * @return AdhesionProposal
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
     * @return AdhesionProposal
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
