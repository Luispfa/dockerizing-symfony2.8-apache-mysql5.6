<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Groups;

/**
 * TipoVoto
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="voto_unique", columns={"tipo"})})
 * @ORM\Entity(repositoryClass="Lugh\WebAppBundle\Repository\CustomRepository")
 * @ExclusionPolicy("all")
 */
class TipoVoto
{
    /**
     *
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="VotoSerie", mappedBy="tipoVoto", cascade={"persist"})
     */
    private $votacionSerie;
    
    /**
     * @ORM\OneToMany(targetEntity="PuntoDia", mappedBy="tipoVoto", cascade={"persist"})
     */
    private $puntos;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="tipo", type="integer", options={"default":0})
     * @Expose
     */
    private $tipo;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=36, nullable=true)
     * @Expose
     */
    private $name;
    
    /**
     * @var text
     *
     * @ORM\Column(name="tag", type="text", nullable=true)
     * @Expose
     */
    private $tag;
    
    /**
     * @var string
     *
     * @ORM\Column(name="claseDecrypt", type="string", length=36, nullable=true)
     */
    private $claseDecrypt;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_serie", type="boolean", options={"default":false})
     */
    private $is_serie;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="maxVotos", type="integer", nullable = true, options={"default":99999999})
     * @Expose
     */
    private $maxVotos;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="minVotos", type="integer", options={"default":1})
     * @Expose
     */
    private $minVotos;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votacionSerie = new \Doctrine\Common\Collections\ArrayCollection();
        $this->puntos = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"OpcionesVoto"})
     * @SerializedName("opcionesVoto")
     */
    
    public function getOpcionesVoto()
    {
        $opcionesVoto = array();
        foreach ($this->puntos as $punto) {
            $opcionesVoto = array_merge
                    (
                        $opcionesVoto, 
                        $punto->getGruposOV()->getOpcionesVoto()->filter(function($element)use($opcionesVoto){return !in_array($element, $opcionesVoto);})->toArray()
                    );
        }
        return $opcionesVoto;
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
     * Set tipo
     *
     * @param integer $tipo
     * @return TipoVoto
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
     * Set is_serie
     *
     * @param boolean $isSerie
     * @return TipoVoto
     */
    public function setIsSerie($isSerie)
    {
        $this->is_serie = $isSerie;

        return $this;
    }

    /**
     * Get is_serie
     *
     * @return boolean 
     */
    public function getIsSerie()
    {
        return $this->is_serie;
    }

    /**
     * Set maxVotos
     *
     * @param integer $maxVotos
     * @return TipoVoto
     */
    public function setMaxVotos($maxVotos)
    {
        $this->maxVotos = $maxVotos;

        return $this;
    }

    /**
     * Get maxVotos
     *
     * @return integer 
     */
    public function getMaxVotos()
    {
        return $this->maxVotos;
    }

    /**
     * Set minVotos
     *
     * @param integer $minVotos
     * @return TipoVoto
     */
    public function setMinVotos($minVotos)
    {
        $this->minVotos = $minVotos;

        return $this;
    }

    /**
     * Get minVotos
     *
     * @return integer 
     */
    public function getMinVotos()
    {
        return $this->minVotos;
    }

    /**
     * Add votacionSerie
     *
     * @param \Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie
     * @return TipoVoto
     */
    public function addVotacionSerie(\Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie)
    {
        $this->votacionSerie[] = $votacionSerie;

        return $this;
    }

    /**
     * Remove votacionSerie
     *
     * @param \Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie
     */
    public function removeVotacionSerie(\Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie)
    {
        $this->votacionSerie->removeElement($votacionSerie);
    }

    /**
     * Get votacionSerie
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotacionSerie()
    {
        return $this->votacionSerie;
    }

    /**
     * Add puntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $puntos
     * @return TipoVoto
     */
    public function addPunto(\Lugh\WebAppBundle\Entity\PuntoDia $puntos)
    {
        $this->puntos[] = $puntos;

        return $this;
    }

    /**
     * Remove puntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $puntos
     */
    public function removePunto(\Lugh\WebAppBundle\Entity\PuntoDia $puntos)
    {
        $this->puntos->removeElement($puntos);
    }

    /**
     * Get puntos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPuntos()
    {
        return $this->puntos;
    }

    /**
     * Set claseDecrypt
     *
     * @param string $claseDecrypt
     * @return TipoVoto
     */
    public function setClaseDecrypt($claseDecrypt)
    {
        $this->claseDecrypt = $claseDecrypt;

        return $this;
    }

    /**
     * Get claseDecrypt
     *
     * @return string 
     */
    public function getClaseDecrypt()
    {
        return $this->claseDecrypt;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TipoVoto
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return TipoVoto
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }
}
