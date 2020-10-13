<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * VotoAbsAdicional
 *
 * @ORM\Table()
 * @ORM\Entity
 *
 * @ExclusionPolicy("all")
 */
class VotoAbsAdicional
{
    const nameClass = 'VotoAbsAdicional';
    const appClass  = 'Voto';
    
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="AbsAdicional")
     * @ORM\JoinColumn(name="absAdicional_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $absAdicional;
    
    /**
     * @ORM\ManyToOne(targetEntity="OpcionesVoto")
     * @ORM\JoinColumn(name="opcionVoto_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $opcionVoto;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accion", inversedBy="votoAbsAdicional")
     * @ORM\JoinColumn(name="accion_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $accion;


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
     * Set opcionVoto
     *
     * @param \Lugh\WebAppBundle\Entity\OpcionesVoto $opcionVoto
     * @return VotoAbsAdicional
     */
    public function setOpcionVoto(\Lugh\WebAppBundle\Entity\OpcionesVoto $opcionVoto)
    {
        $this->opcionVoto = $opcionVoto;

        return $this;
    }

    /**
     * Get opcionVoto
     *
     * @return \Lugh\WebAppBundle\Entity\OpcionesVoto 
     */
    public function getOpcionVoto()
    {
        return $this->opcionVoto;
    }





    /**
     * Set absAdicional
     *
     * @param \Lugh\WebAppBundle\Entity\AbsAdicional $absAdicional
     * @return VotoAbsAdicional
     */
    public function setAbsAdicional(\Lugh\WebAppBundle\Entity\AbsAdicional $absAdicional)
    {
        $this->absAdicional = $absAdicional;

        return $this;
    }

    /**
     * Get absAdicional
     *
     * @return \Lugh\WebAppBundle\Entity\AbsAdicional 
     */
    public function getAbsAdicional()
    {
        return $this->absAdicional;
    }

    /**
     * Set accion
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accion
     * @return VotoAbsAdicional
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
}
