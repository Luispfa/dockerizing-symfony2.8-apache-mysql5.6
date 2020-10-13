<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\Response;
use Lugh\WebAppBundle\Lib\External\StoreManager;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * Communique
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Communique
{
    const nameClass = 'Communique';
    const appClass  = 'Derecho';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="communiques")
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=true)
     * @Expose
     */
    private $autor;
    
    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="communique", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"Documents"}) 
     */
    private $documents;

    /**
     * @ORM\Column(name="subject", type="string", length=255)
     * @Expose
     */
    private $subject;

    /**
     * @ORM\Column(name="body", type="text")
     * @Expose
     */
    private $body;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $enabled;
    
    /**
     *
     *  enabledState
     */
    private $enabledState;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    public function setEnabledState($enabled)
    {
        if ($enabled == null)
        {
            $this->enabledState = $this->getContainer()->get('lugh.server')->getState()->getEnableState();
        }
        switch ($this->getEnabled()) {
            case StateClass::enable:
                $this->enabledState = $this->getContainer()->get('lugh.server')->getState()->getEnableState();
                break;
            case StateClass::disable:
                $this->enabledState = $this->getContainer()->get('lugh.server')->getState()->getDisableState();
                break;
            default:
                break;
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setEnabledState($this->getEnabled());
    }
    
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setEnabledState($this->getEnabled());
    }
    
    public function enable() {
        return $this->enabledState->enable($this);
    }
    public function disable() {
        return $this->enabledState->disable($this);
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
     * Add documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     * @return Communique
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
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\User $autor
     * @return Communique
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


    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Communique
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Communique
     */
    public function setSubject($subject)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $this->subject = $behavior->formatString($subject);

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
     * @return Communique
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
     * @return Communique
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
