<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Criteria;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Delegado
 *
 * @ORM\Table()
 * @ORM\Entity
 * 
 * @ExclusionPolicy("all")
 */
class Delegado
{
    const nameClass = 'Delegado';
    const appClass  = 'Voto';
    
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=250)
     * @Expose
     */
    private $nombre;
    
    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="nombreTrans", type="string", length=250, nullable=true)
     * @Expose
     */
    private $nombreTrans;

    /**
     * @var string
     *
     * @ORM\Column(name="documentNum", type="string", length=50)
     * @Expose
     */
    private $documentNum;

    /**
     * @var string
     *
     * @ORM\Column(name="documentType", type="string", length=10)
     * @Expose
     */
    private $documentType;
    
    /**
     * @var string
     * @ORM\Column(name="email", type="string", nullable=true)
     * @Expose
     * @Groups({"mail"}) 
     */
    protected $email;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_conseller", type="boolean", options={"default":false})
     * @Expose
     * @Groups({"conseller"}) 
     */
    private $isConseller = false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="has_delegationLimit", type="boolean", options={"default":false})
     */
    private $hasDelegationLimit = false;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="maxDelegations", type="integer", options={"default":0})
     */
    private $maxDelegations = 0;
    
    /**
     * @var FicheroAccionistas
     * @ORM\OneToOne(targetEntity="FicheroAccionistas", fetch="EAGER", orphanRemoval=true)
     * @ORM\JoinColumn(name="ficheroaccionista_id", referencedColumnName="id")
     **/
    private $ficheroAccionista = null;
    
    /**
     * @ORM\OneToMany(targetEntity="Delegacion", mappedBy="delegado", cascade={"persist"})
     * @ORM\OrderBy({"dateTime" = "DESC"})
     */
    private $delegacion;
    
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=250, nullable=true)
     * @Expose
     * @Groups({"VarMail"}) 
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_director", type="boolean", options={"default":false})
     * @Expose
     * @Groups({"conseller"}) 
     */
    private $isDirector = false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_secretary", type="boolean", options={"default":false})
     * @Expose
     * @Groups({"conseller"}) 
     */
    private $isSecretary = false;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

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
     * Set nombre
     *
     * @param string $nombre
     * @return Delegado
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set ficheroAccionista
     *
     * @param \Lugh\WebAppBundle\Entity\FicheroAccionistas $ficheroAccionista
     * @return Delegado
     */
    public function setFicheroAccionista(\Lugh\WebAppBundle\Entity\FicheroAccionistas $ficheroAccionista = null)
    {
        $this->ficheroAccionista = $ficheroAccionista;

        return $this;
    }

    /**
     * Get ficheroAccionista
     *
     * @return \Lugh\WebAppBundle\Entity\FicheroAccionistas 
     */
    public function getFicheroAccionista()
    {
        return $this->ficheroAccionista;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->delegacion = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add delegacion
     *
     * @param \Lugh\WebAppBundle\Entity\Delegacion $delegacion
     * @return Delegado
     */
    public function addDelegacion(\Lugh\WebAppBundle\Entity\Delegacion $delegacion)
    {
        $this->delegacion[] = $delegacion;

        return $this;
    }

    /**
     * Remove delegacion
     *
     * @param \Lugh\WebAppBundle\Entity\Delegacion $delegacion
     */
    public function removeDelegacion(\Lugh\WebAppBundle\Entity\Delegacion $delegacion)
    {
        $this->delegacion->removeElement($delegacion);
    }

    /**
     * Get delegacion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegacion()
    {
        return $this->delegacion;
    }
    
    public function getAccionistasDelegacion()
    {
        $accionistas = $this->delegacion->map(function($element)
        {
            return $element->getAccionista();
            
        })->toArray();
        return array_unique($accionistas, SORT_REGULAR );
        
    }

    /**
     * Set isConseller
     *
     * @param boolean $isConseller
     * @return Delegado
     */
    public function setIsConseller($isConseller)
    {
        $this->isConseller = $isConseller;

        return $this;
    }

    /**
     * Get isConseller
     *
     * @return boolean 
     */
    public function getIsConseller()
    {
        return $this->isConseller;
    }

    /**
     * Set hasDelegationLimit
     *
     * @param boolean $hasDelegationLimit
     * @return Delegado
     */
    public function setHasDelegationLimit($hasDelegationLimit)
    {
        $this->hasDelegationLimit = $hasDelegationLimit;

        return $this;
    }

    /**
     * Get hasDelegationLimit
     *
     * @return boolean 
     */
    public function getHasDelegationLimit()
    {
        return $this->hasDelegationLimit;
    }

    /**
     * Set maxDelegations
     *
     * @param integer $maxDelegations
     * @return Delegado
     */
    public function setMaxDelegations($maxDelegations)
    {
        $this->maxDelegations = $maxDelegations;

        return $this;
    }

    /**
     * Get maxDelegations
     *
     * @return integer 
     */
    public function getMaxDelegations()
    {
        return $this->maxDelegations;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Delegado
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

    /**
     * Set email
     *
     * @param string $email
     * @return Delegado
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set documentNum
     *
     * @param string $documentNum
     * @return Delegado
     */
    public function setDocumentNum($documentNum)
    {
        $this->documentNum = $documentNum;

        return $this;
    }

    /**
     * Get documentNum
     *
     * @return string 
     */
    public function getDocumentNum()
    {
        return $this->documentNum;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return Delegado
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string 
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set isDirector
     *
     * @param boolean $isDirector
     * @return Delegado
     */
    public function setIsDirector($isDirector)
    {
        $this->isDirector = $isDirector;

        return $this;
    }

    /**
     * Get isDirector
     *
     * @return boolean 
     */
    public function getIsDirector()
    {
        return $this->isDirector;
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Set nombreTrans
     *
     * @param string $nombreTrans
     * @return Delegado
     */
    public function setNombreTrans($nombreTrans)
    {
        $this->nombreTrans = $nombreTrans;

        return $this;
    }

    /**
     * Get nombreTrans
     *
     * @return string 
     */
    public function getNombreTrans()
    {
        return $this->nombreTrans;
    }

    /**
     * Set isSecretary
     *
     * @param boolean $isSecretary
     * @return Delegado
     */
    public function setIsSecretary($isSecretary)
    {
        $this->isSecretary = $isSecretary;

        return $this;
    }

    /**
     * Get isSecretary
     *
     * @return boolean 
     */
    public function getIsSecretary()
    {
        return $this->isSecretary;
    }
}
