<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Message
{
    const nameClass = 'Message';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="ItemAccionista", inversedBy="messages")
     * @Expose
     * @Groups({"itemAccionista"}) 
     */
    private $itemAccionista;
    
    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="messages")
     * @Expose
     * @Groups({"proposal", "VarMail"}) 
     */
    private $proposal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="messages")
     * @Expose
     * @Groups({"offer", "VarMail"}) 
     */
    private $offer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Initiative", inversedBy="messages")
     * @Expose
     * @Groups({"initiative", "VarMail"}) 
     */
    private $initiative;
    
    /**
     * @ORM\ManyToOne(targetEntity="Request", inversedBy="messages")
     * @Expose
     * @Groups({"request", "VarMail"}) 
     */
    private $request;
    
    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="messages")
     * @Expose
     * @Groups({"thread", "VarMail"}) 
     */
    private $thread;
    
    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="messages")
     * @Expose
     * @Groups({"question", "VarMail"}) 
     */
    private $question;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $autor;
    
    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Expose
     */
    private $body;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="message", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"documents"}) 
     */
    protected $documents;
    
    /**
     * @ORM\ManyToOne(targetEntity="App", inversedBy="messages")
     * @Expose
     * @Groups({"app"}) 
     */
    private $app;
    
    /** 
     * @ORM\PrePersist 
     */
    public function doUniqueItemOnPrePersist()
    {
        if ($this->hasMultipleItems())
        {
            throw new Exception("Message has multiple Items");
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
     * Set body
     *
     * @param string $body
     * @return Message
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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Message
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
     * Set itemAccionista
     *
     * @param \Lugh\WebAppBundle\Entity\ItemAccionista $itemAccionista
     * @return Message
     */
    public function setItemAccionista(\Lugh\WebAppBundle\Entity\ItemAccionista $itemAccionista = null)
    {
        $this->itemAccionista = $itemAccionista;

        return $this;
    }

    /**
     * Get itemAccionista
     *
     * @return \Lugh\WebAppBundle\Entity\ItemAccionista 
     */
    public function getItemAccionista()
    {
        return $this->itemAccionista;
    }

    /**
     * Set proposal
     *
     * @param \Lugh\WebAppBundle\Entity\Proposal $proposal
     * @return Message
     */
    public function setProposal(\Lugh\WebAppBundle\Entity\Proposal $proposal = null)
    {
        $this->proposal = $this->restrictionsItem($proposal);

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
     * Set offer
     *
     * @param \Lugh\WebAppBundle\Entity\Offer $offer
     * @return Message
     */
    public function setOffer(\Lugh\WebAppBundle\Entity\Offer $offer = null)
    {
        $this->offer = $this->restrictionsItem($offer);

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
     * Set initiative
     *
     * @param \Lugh\WebAppBundle\Entity\Initiative $initiative
     * @return Message
     */
    public function setInitiative(\Lugh\WebAppBundle\Entity\Initiative $initiative = null)
    {
        $this->initiative = $this->restrictionsItem($initiative);

        return $this;
    }

    /**
     * Get initiative
     *
     * @return \Lugh\WebAppBundle\Entity\Initiative 
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * Set request
     *
     * @param \Lugh\WebAppBundle\Entity\Request $request
     * @return Message
     */
    public function setRequest(\Lugh\WebAppBundle\Entity\Request $request = null)
    {
        $this->request = $this->restrictionsItem($request);

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
     * Set thread
     *
     * @param \Lugh\WebAppBundle\Entity\Thread $thread
     * @return Message
     */
    public function setThread(\Lugh\WebAppBundle\Entity\Thread $thread = null)
    {
        $this->thread = $this->restrictions($thread);

        return $this;
    }

    /**
     * Get thread
     *
     * @return \Lugh\WebAppBundle\Entity\Thread 
     */
    public function getThread()
    {
        return $this->thread;
    }
    
    /**
     * Set question
     *
     * @param \Lugh\WebAppBundle\Entity\Question $question
     * @return Message
     */
    public function setQuestion(\Lugh\WebAppBundle\Entity\Question $question = null)
    {
        $this->question = $this->restrictions($question);

        return $this;
    }

    /**
     * Get question
     *
     * @return \Lugh\WebAppBundle\Entity\Question 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\User $autor
     * @return Message
     */
    public function setAutor(\Lugh\WebAppBundle\Entity\User $autor = null)
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return \Lugh\WebAppBundle\Entity\User 
     */
    public function getAutor()
    {
        return $this->autor;
    }
    //@TODO solo puede tener un id item
    
    private function restrictions($item, $action= StateClass::actionCreate)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->hasUserPermission($item, $action);
        $behavior->hasUserPermissionWriteMessage($item, $action);
        return $item;    
    }
    
    private function restrictionsItem($item, $action= StateClass::actionCreate)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->hasUserPermissionItem($item, $action);
        return $item;
    }
    
    private function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    public function getItem()
    {
        $methods = array
                (
                0   =>  'getThread',
                1   =>  'getInitiative',
                2   =>  'getOffer',
                3   =>  'getProposal',
                4   =>  'getRequest'
                );
        
        foreach ($methods as $method) {
            if ($this->{$method}() != null)
            {
                return $this->{$method}();
            }
        }
        return null;
    }
    
    private function hasMultipleItems()
    {
        $hasItem = false;
        $methods = array
                (
                0   =>  'getThread',
                1   =>  'getInitiative',
                2   =>  'getOffer',
                3   =>  'getProposal',
                4   =>  'getRequest'
                );
        
        foreach ($methods as $method) {
            if ($this->{$method}() != null)
            {
                if ($hasItem)
                {
                    return true;
                }
                $hasItem = true;
            }
        }
        return false;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->document = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Add documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     * @return Message
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
    
    /**
     * Set App
     *
     * @param \Lugh\WebAppBundle\Entity\App $app
     * @return Message
     */
    public function setApp(\Lugh\WebAppBundle\Entity\App $app = null)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Get App
     *
     * @return \Lugh\WebAppBundle\Entity\App 
     */
    public function getApp()
    {
        return $this->app;
    }
}
