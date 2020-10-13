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
 * AbsAdicional
 *
 * @ORM\Table()
 * @ORM\Entity
 * 
 * 
 * @ExclusionPolicy("all")
 */
class AbsAdicional {
    const nameClass = 'AbsAdicional';
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
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="text", type="text")
     * @Expose
     */
    private $text;
    

    /**
     * @var integer
     *
     * @ORM\Column(name="voteProxy", type="integer", options={"default" = 0})
     * @Expose
     */
    private $voteProxy;

    
    /**
     * @ORM\ManyToOne(targetEntity="TipoVoto", inversedBy="puntos", cascade={"persist"})
     * @ORM\JoinColumn(name="tipoVoto_id", referencedColumnName="id", nullable=false)
     * @Expose
     */ 
    private $tipoVoto;
    
    /**
     * @ORM\ManyToOne(targetEntity="GrupoOpcionesVoto", inversedBy="puntos", cascade={"persist"})
     * @ORM\JoinColumn(name="gruposOV_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $gruposOV;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity="VotoAbsAdicional", mappedBy="absAdicional", cascade={"persist", "remove"})
     * 
     * @Expose
     */
    private $votoAbsAdicional;
    

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
     * @return AbsAdicional
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
     * Set voteProxy
     *
     * @param integer $voteProxy
     * @return AbsAdicional
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
     * Set tipoVoto
     *
     * @param \Lugh\WebAppBundle\Entity\TipoVoto $tipoVoto
     * @return AbsAdicional
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
     * @return AbsAdicional
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
     * Constructor
     */
    public function __construct()
    {
        $this->votoAbsAdicional = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add votoAbsAdicional
     *
     * @param \Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional
     * @return AbsAdicional
     */
    public function addVotoAbsAdicional(\Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional)
    {
        $this->votoAbsAdicional[] = $votoAbsAdicional;

        return $this;
    }

    /**
     * Remove votoAbsAdicional
     *
     * @param \Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional
     */
    public function removeVotoAbsAdicional(\Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional)
    {
        $this->votoAbsAdicional->removeElement($votoAbsAdicional);
    }

    /**
     * Get votoAbsAdicional
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotoAbsAdicional()
    {
        return $this->votoAbsAdicional;
    }
}
