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

/**
 * Question
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Question extends Item
{
    const nameClass = 'Question';
    const appClass  = 'AV';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="question", cascade={"persist", "remove"})
     * @ORM\OrderBy({"dateTime" = "ASC"})
     * @Expose
     * @Type("array") 
     * @Accessor(getter="getMessages")
     * @Groups({"messages"}) 
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="question", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"DocumentsQuestions"})
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="questions", cascade={"persist"})
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $autor;

    /**
     * @ORM\Column(name="subject",type="string",length=255)
     * @Expose
     */
    private $subject;
    
    /**
     * @ORM\Column(type="text")
     * @Expose
     */
    
    private $body;

    
    
    
    
    public function __construct() {
        $this->setItemState($this->getState());
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

   /* public function locked($comments = null) {
        return $this->lockedState->locked($this, $comments);
    }
    public function unlocked($comments = null) {
        return $this->lockedState->unlocked($this, $comments);
    }*/

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
     * Add messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     * @return Thread
     */
    public function addMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $messages->setQuestion($this);

        $this->messages[] = $messages;
        
        $mailer = $this->getContainer()->get('lugh.server')->getMailer();
        $mailer->formatandsend($messages, StateClass::actionAdd);

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
        $this->restrictions($this);
        return $this->messages;
    }
    
    private function restrictions($item, $action= StateClass::actionGet)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->hasUserPermission($item, $action);
    }

    /**
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $autor
     * @return Thread
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
     * Set subject
     *
     * @param string $subject
     * @return Thread
     */
    public function setSubject($subject)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->subject = $behavior->formatString($behavior->noContent($subject));
        
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Thread
     */
    public function setBody($body)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->body = $behavior->formatString($body);

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Add documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     * @return Question
     */
    public function addDocument(\Lugh\WebAppBundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     */
    public function removeDocument(\Lugh\WebAppBundle\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
