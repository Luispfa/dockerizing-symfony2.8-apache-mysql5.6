<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Proposal
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Proposal extends Item
{
    const nameClass = 'Proposal';
    const appClass  = 'Foro';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="AdhesionProposal", mappedBy="proposal", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"adhesions"}) 
     */
    private $adhesions;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="proposal", cascade={"persist", "remove"})
     * @ORM\OrderBy({"dateTime" = "DESC"})
     * @Expose
     * @Groups({"messages"})
     * @Type("array") 
     * @Accessor(getter="getMessages")
     */
    private $messages;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="proposals", cascade={"persist"})
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $autor;

    /**
     * @ORM\Column(name="justification", type="text")
     * @Expose
     */
    private $justification;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Expose
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="text")
     * @Expose
     */
    private $description;
    
    
    public function __construct() {
        $this->setItemState($this->getState());
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"NumAdhesion"}) 
     * @SerializedName("numAdhesions")
     */
    
    public function getNumAdhesions()
    {
        return count($this->getAdhesions());
    }
    
        
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setItemState($this->getState());
    }
    public function pendiente($comments = null) {
        return $this->itemState->pendiente($this, $comments);
    }
    public function publica($comments = null) {
        return $this->itemState->publica($this, $comments);
    }
    public function retorna($comments = null) {
        return $this->itemState->retorna($this, $comments);
    }
    public function rechaza($comments = null) {
        return $this->itemState->rechaza($this, $comments);
    }
    
    public function preSave()
    {
        parent::preSave();
        if ($this->adhesions != null)
        {
            foreach ($this->adhesions as $adhesion) {
                $adhesion->preSave();
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
     * Add adhesions
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionProposal $adhesions
     * @return Proposal
     */
    public function addAdhesion(\Lugh\WebAppBundle\Entity\AdhesionProposal $adhesions)
    {
        $adhesions->setProposal($this);
        $this->adhesions[] = $adhesions;

        return $this;
    }

    /**
     * Remove adhesions
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionProposal $adhesions
     */
    public function removeAdhesion(\Lugh\WebAppBundle\Entity\AdhesionProposal $adhesions)
    {
        $this->adhesions->removeElement($adhesions);
    }

    /**
     * Get adhesions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdhesions()
    {
        return $this->restrictions($this->adhesions);
    }

    /**
     * Add messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     * @return Proposal
     */
    public function addMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $messages->setProposal($this);
        $this->messages[] = $messages;

        return $this;
    }
    
    public function addMessageWihtMail(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $messages->setProposal($this);
        $this->messages[] = $messages;
        
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->mailerAction($messages,StateClass::actionAdd, self::nameClass, array(), true);

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
        return $this->restrictionMessage($this->messages);
    }
    
    private function restrictions($items, $action= StateClass::actionGet)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->filterUserPermissionAdhesion($items, $action);
    }
    
    private function restrictionMessage($items, $action= StateClass::actionGet)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->filterUserOwnerMessage($items, $action);
    }
    

    /**
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $autor
     * @return Proposal
     */
    public function setAutor(\Lugh\WebAppBundle\Entity\Accionista $autor)
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return \Lugh\WebAppBundle\Entity\Accionista 
     */
    public function getAutor()
    {
        return $this->autor;
    }

    /**
     * Set justification
     *
     * @param string $justification
     * @return Proposal
     */
    public function setJustification($justification)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->justification = $behavior->formatString($justification);

        return $this;
    }

    /**
     * Get justification
     *
     * @return string 
     */
    public function getJustification()
    {
        return $this->justification;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Proposal
     */
    public function setTitle($title)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->title = $behavior->formatString($behavior->noContent($title));
        
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Proposal
     */
    public function setDescription($description)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->description = $behavior->formatString($description);

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
}
