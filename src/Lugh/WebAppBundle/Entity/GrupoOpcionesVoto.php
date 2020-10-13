<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;    
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * GrupoOpcionesVoto
 *
 * @ORM\Table()
 * @ORM\Entity
* @ExclusionPolicy("all")
 */
class GrupoOpcionesVoto
{
    const nameClass = 'GrupoOpcionesVoto';
    const appClass  = 'Voto';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name", type="string", length=36, nullable=true)
     */
    private $name;
    
    /**
     * @ORM\OneToMany(targetEntity="PuntoDia", mappedBy="gruposOV")
     */
    private $puntos;
    
    /**
     * @ORM\ManyToMany(targetEntity="OpcionesVoto", inversedBy="grupoOpcionesVoto")
     * @ORM\OrderBy({"orden" = "ASC"})
     * @ORM\JoinTable(name="opcionesVotoPorGrupo")
     * @Expose
     **/
    private $opcionesVoto;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->puntos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->opcionesVoto = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add puntos
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $puntos
     * @return GrupoOpcionesVoto
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
     * Add opcionesVoto
     *
     * @param \Lugh\WebAppBundle\Entity\OpcionesVoto $opcionesVoto
     * @return GrupoOpcionesVoto
     */
    public function addOpcionesVoto(\Lugh\WebAppBundle\Entity\OpcionesVoto $opcionesVoto)
    {
        $this->opcionesVoto[] = $opcionesVoto;

        return $this;
    }

    /**
     * Remove opcionesVoto
     *
     * @param \Lugh\WebAppBundle\Entity\OpcionesVoto $opcionesVoto
     */
    public function removeOpcionesVoto(\Lugh\WebAppBundle\Entity\OpcionesVoto $opcionesVoto)
    {
        $this->opcionesVoto->removeElement($opcionesVoto);
    }
    
    public function resetOpcionesVoto()
    {
        foreach ($this->opcionesVoto as $opcionVoto) {
            $this->removeOpcionesVoto($opcionVoto);
        }
    }

    /**
     * Get opcionesVoto
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpcionesVoto()
    {
        return $this->opcionesVoto;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return GrupoOpcionesVoto
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
}
