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
 * PuntoDia
 *
 * @ORM\Entity(repositoryClass="Lugh\WebAppBundle\Repository\CustomRepository")
 * 
 * 
 * @ExclusionPolicy("all")
 */
class PuntoDia {
    const nameClass = 'PuntoDia';
    const appClass  = 'Voto';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * 
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="PuntoDia", inversedBy="subpuntos")
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="PuntoDia", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({"orden" = "ASC"})
     * 
     * @Expose
     */
    private $subpuntos;
    
    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="text", type="text")
     * @Expose
     */
    private $text;
    
    /**
     * @var string
     *
     * @ORM\Column(name="numPunto", type="string", length=255)
     * @Expose
     */
    private $numPunto;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="retirado", type="boolean", options={"default" = false})
     * @Expose
     */
    private $retirado;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="informativo", type="boolean", options={"default" = false})
     * @Expose
     */
    private $informativo;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="extra", type="integer", options={"default" = 0})
     * @Expose
     */
    private $extra;

    /**
     * @var integer
     *
     * @ORM\Column(name="voteProxy", type="integer", options={"default" = 0})
     * @Expose
     */
    private $voteProxy;

    /**
     * @var integer
     *
     * @ORM\Column(name="idFile", type="integer", nullable=true)
     * @Expose
     */
    private $idFile;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=false)
     * @Expose
     */
    private $orden;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoVoto", inversedBy="puntos", cascade={"persist"})
     * @ORM\JoinColumn(name="tipoVoto_id", referencedColumnName="id", nullable=false)
     * @Expose
     * @Groups({"tipoVoto"}) 
     */
    private $tipoVoto;
    
    /**
     * @ORM\ManyToOne(targetEntity="GrupoOpcionesVoto", inversedBy="puntos", cascade={"persist"})
     * @ORM\JoinColumn(name="gruposOV_id", referencedColumnName="id", nullable=false)
     * @Expose
     * @Groups({"opcionesVoto"}) 
     */
    private $gruposOV;
    
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
     * Set text
     *
     * @param string $text
     * @return PuntoDia
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set numPunto
     *
     * @param string $numPunto
     * @return PuntoDia
     */
    public function setNumPunto($numPunto)
    {
        $this->numPunto = $numPunto;

        return $this;
    }

    /**
     * Get numPunto
     *
     * @return string 
     */
    public function getNumPunto()
    {
        return $this->numPunto;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subpuntos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set parent
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $parent
     * @return PuntoDia
     */
    public function setParent(\Lugh\WebAppBundle\Entity\PuntoDia $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Lugh\WebAppBundle\Entity\PuntoDia 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add subpuntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $subpuntos
     * @return PuntoDia
     */
    public function addSubpunto(\Lugh\WebAppBundle\Entity\PuntoDia $subpuntos)
    {
        $subpuntos->setParent($this);
        $this->subpuntos[] = $subpuntos;

        return $this;
    }

    /**
     * Remove subpuntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $subpuntos
     */
    public function removeSubpunto(\Lugh\WebAppBundle\Entity\PuntoDia $subpuntos)
    {
        $this->subpuntos->removeElement($subpuntos);
    }
    
    /**
     * Set subpuntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $subpuntos
     * @return PuntoDia
     */
    public function setSubpuntos(\Doctrine\Common\Collections\Collection $subpuntos)
    {
        $this->subpuntos = $subpuntos;

        return $this;
    }
    
    public function getSubpuntosFilter()
    {
        $criteria = Criteria::create()
        ->where(Criteria::expr()->eq("retirado", false))
        ->orderBy(array("orden" => Criteria::ASC));
        
        return $this->subpuntos->matching($criteria);
    }

    /**
     * Get subpuntos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubpuntos()
    {
        return $this->subpuntos;
    }

    /**
     * Set retirado
     *
     * @param boolean $retirado
     * @return PuntoDia
     */
    public function setRetirado($retirado)
    {
        $this->retirado = $retirado;

        return $this;
    }

    /**
     * Get retirado
     *
     * @return boolean 
     */
    public function getRetirado()
    {
        return $this->retirado;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return PuntoDia
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set tipoVoto
     *
     * @param \Lugh\WebAppBundle\Entity\TipoVoto $tipoVoto
     * @return PuntoDia
     */
    public function setTipoVoto(\Lugh\WebAppBundle\Entity\TipoVoto $tipoVoto)
    {
        $this->tipoVoto = $tipoVoto;

        return $this;
    }

    /**
     * Get tipoVoto
     *
     * @return \Lugh\WebAppBundle\Entity\TipoVoto 
     */
    public function getTipoVoto()
    {
        return $this->tipoVoto;
    }

    /**
     * Set gruposOV
     *
     * @param \Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $gruposOV
     * @return PuntoDia
     */
    public function setGruposOV(\Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $gruposOV)
    {
        $this->gruposOV = $gruposOV;

        return $this;
    }

    /**
     * Get gruposOV
     *
     * @return \Lugh\WebAppBundle\Entity\GrupoOpcionesVoto 
     */
    public function getGruposOV()
    {
        return $this->gruposOV;
    }

    /**
     * Set informativo
     *
     * @param boolean $informativo
     * @return PuntoDia
     */
    public function setInformativo($informativo)
    {
        $this->informativo = $informativo;

        return $this;
    }

    /**
     * Get informativo
     *
     * @return boolean 
     */
    public function getInformativo()
    {
        return $this->informativo;
    }

    /**
     * Set extra
     *
     * @param integer $extra
     * @return PuntoDia
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return integer 
     */
    public function getExtra()
    {
        return $this->extra;
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Set voteProxy
     *
     * @param integer $voteProxy
     * @return PuntoDia
     */
    public function setVoteProxy($voteProxy)
    {
        $this->voteProxy = $voteProxy;

        return $this;
    }

    /**
     * Get voteProxy
     *
     * @return integer 
     */
    public function getVoteProxy()
    {
        return $this->voteProxy;
    }

    /**
     * Set idFile
     *
     * @param integer $idFile
     * @return PuntoDia
     */
    public function setIdFile($idFile)
    {
        $this->idFile = $idFile;

        return $this;
    }

    /**
     * Get idFile
     *
     * @return integer 
     */
    public function getIdFile()
    {
        return $this->idFile;
    }
}
