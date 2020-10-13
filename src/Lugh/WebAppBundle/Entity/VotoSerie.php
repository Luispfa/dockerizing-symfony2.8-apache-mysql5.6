<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * VotoPunto
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="voto_unique", columns={"accion_id", "tipoVoto_id"})})
 * @ORM\Entity
 * 
 * @ExclusionPolicy("all")
 */
class VotoSerie
{
    const nameClass = 'VotoSerie';
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
     * @ORM\ManyToOne(targetEntity="Accion", inversedBy="votacionSerie")
     * @ORM\JoinColumn(name="accion_id", referencedColumnName="id", nullable=false)
     */
    private $accion;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoVoto", inversedBy="votacionSerie")
     * @ORM\JoinColumn(name="tipoVoto_id", referencedColumnName="id", nullable=false)
     */
    private $tipoVoto;
    
    /** 
     * @ORM\Column(type="text")
     * @Expose
     */
    private $voto;
    
    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $algorithm;

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
     * Set voto
     *
     * @param string $voto
     * @return VotoSerie
     */
    public function setVoto($voto)
    {
        $this->voto = $voto;

        return $this;
    }

    /**
     * Get voto
     *
     * @return string 
     */
    public function getVoto()
    {
        return $this->voto;
    }

    /**
     * Set accion
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accion
     * @return VotoSerie
     */
    public function setAccion(\Lugh\WebAppBundle\Entity\Accion $accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return \Lugh\WebAppBundle\Entity\Accion 
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set tipoVoto
     *
     * @param \Lugh\WebAppBundle\Entity\TipoVoto $tipoVoto
     * @return VotoSerie
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
     * Set algorithm
     *
     * @param string $algorithm
     * @return VotoSerie
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Get algorithm
     *
     * @return string 
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }
}
