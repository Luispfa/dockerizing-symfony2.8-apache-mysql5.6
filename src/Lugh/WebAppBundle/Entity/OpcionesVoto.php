<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * OpcionesVoto
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class OpcionesVoto
{
    const nameClass = 'OpcionesVoto';
    const appClass  = 'Voto';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Expose
     */
    private $nombre;
    
    /**
     * @var string
     *
     * @ORM\Column(name="symbol", type="string", length=1, nullable=false, unique=true)
     * @Expose
     */
    private $symbol;


    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=false, unique=true)
     * @Expose
     */
    private $orden;

    /**
     * @ORM\OneToMany(targetEntity="VotoAbsAdicional", mappedBy="opcionVoto", cascade={"persist", "remove"})
     * @Expose
     */
    private $votoAbsAdicional;
    
    /**
     * @ORM\ManyToMany(targetEntity="GrupoOpcionesVoto", mappedBy="opcionesVoto")
     **/
    private $grupoOpcionesVoto;
    
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
     * @return OpcionesVoto
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
     * Set symbol
     *
     * @param string $symbol
     * @return OpcionesVoto
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string 
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return OpcionesVoto
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
     * Constructor
     */
    public function __construct()
    {
        $this->grupoOpcionesVoto = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }


    /**
     * Add grupoOpcionesVoto
     *
     * @param \Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $grupoOpcionesVoto
     * @return OpcionesVoto
     */
    public function addGrupoOpcionesVoto(\Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $grupoOpcionesVoto)
    {
        $this->grupoOpcionesVoto[] = $grupoOpcionesVoto;

        return $this;
    }

    /**
     * Remove grupoOpcionesVoto
     *
     * @param \Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $grupoOpcionesVoto
     */
    public function removeGrupoOpcionesVoto(\Lugh\WebAppBundle\Entity\GrupoOpcionesVoto $grupoOpcionesVoto)
    {
        $this->grupoOpcionesVoto->removeElement($grupoOpcionesVoto);
    }

    /**
     * Get grupoOpcionesVoto
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrupoOpcionesVoto()
    {
        return $this->grupoOpcionesVoto;
    }

    /**
     * Add votoAbsAdicional
     *
     * @param \Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional
     * @return OpcionesVoto
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
