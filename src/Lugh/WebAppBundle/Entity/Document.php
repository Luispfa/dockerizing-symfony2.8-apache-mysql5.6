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
 * Document
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class Document
{
    const nameClass = 'Document';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="documents")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     * @Expose
     */
    private $owner;
    
    /**
     * @ORM\ManyToOne(targetEntity="Communique", inversedBy="documents", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="communique_id", referencedColumnName="id", nullable=true)
     * @Expose
     * @Groups({"Communique"}) 
     */
    private $communique;

    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="documents", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=true)
     * @Expose
     * @Groups({"Question"})
     */
    private $question;
    
    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="documents", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=true)
     * @Expose
     * @Groups({"Message"}) 
     */
    private $message;
    
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=250)
     */
    private $token;
   
    /**
     * @var integer
     *
     * @ORM\Column(name="tipo", type="integer", nullable=true)
     */
    private $tipo;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_interno", type="string", length=250)
     */
    private $nombreInterno;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_externo", type="string", length=250)
     * @Expose
     */
    private $nombreExterno;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ownerbkp", type="string", length=250, nullable=true)
     * @Expose
     */
    private $ownerbkp;
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    private function restrictions($item, $action= StateClass::actionGet)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->hasUserPermissionDocument($item, $action);  
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
     * Set owner
     *
     * @param \Lugh\WebAppBundle\Entity\User $owner
     * @return Document
     */
    public function setOwner(\Lugh\WebAppBundle\Entity\User $owner = null)
    {
        $action = ($this->owner == null) ? StateClass::actionStore : StateClass::actionDelete;
        $this->restrictions($this, $action);
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Lugh\WebAppBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set tipo
     *
     * @param integer $tipo
     * @return Document
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return integer 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set nombreInterno
     *
     * @param string $nombreInterno
     * @return Document
     */
    public function setNombreInterno($nombreInterno)
    {
        $this->nombreInterno = $nombreInterno;

        return $this;
    }

    /**
     * Get nombreInterno
     *
     * @return string 
     */
    public function getNombreInterno()
    {
        return $this->nombreInterno;
    }

    /**
     * Set nombreExterno
     *
     * @param string $nombreExterno
     * @return Document
     */
    public function setNombreExterno($nombreExterno)
    {
        $this->nombreExterno = $nombreExterno;

        return $this;
    }

    /**
     * Get nombreExterno
     *
     * @return string 
     */
    public function getNombreExterno()
    {
        return $this->nombreExterno;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Document
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
     * Set token
     *
     * @param string $token
     * @return Document
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
    
    public function getData()
    {
        $response = new Response();
        
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', str_replace(' ', '_', $this->getNombreExterno())));
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->setContent(StoreManager::RetrieveGeneric($this->nombreInterno));
        
        return $response;
    }

    /**
     * Set communique
     *
     * @param \Lugh\WebAppBundle\Entity\Communique $communique
     * @return Document
     */
    public function setCommunique(\Lugh\WebAppBundle\Entity\Communique $communique = null)
    {
        $this->communique = $communique;

        return $this;
    }

    /**
     * Get communique
     *
     * @return \Lugh\WebAppBundle\Entity\Communique 
     */
    public function getCommunique()
    {
        return $this->communique;
    }

    /**
     * Set message
     *
     * @param \Lugh\WebAppBundle\Entity\Message $message
     * @return Document
     */
    public function setMessage(\Lugh\WebAppBundle\Entity\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Lugh\WebAppBundle\Entity\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set ownerbkp
     *
     * @param string $ownerbkp
     * @return Document
     */
    public function setOwnerbkp($ownerbkp)
    {
        $this->ownerbkp = $ownerbkp;

        return $this;
    }

    /**
     * Get ownerbkp
     *
     * @return string 
     */
    public function getOwnerbkp()
    {
        return $this->ownerbkp;
    }

    /**
     * Set question
     *
     * @param \Lugh\WebAppBundle\Entity\Question $question
     * @return Document
     */
    public function setQuestion(\Lugh\WebAppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

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
}
