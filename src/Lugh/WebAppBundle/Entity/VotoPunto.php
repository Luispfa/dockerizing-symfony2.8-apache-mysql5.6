<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * VotoPunto
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="voto_unique", columns={"punto_id", "accion_id"})})
 * @ORM\Entity
 */
class VotoPunto
{
    const nameClass = 'VotoPunto';
    //const appClass  = 'Voto';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="PuntoDia")
     * @ORM\JoinColumn(name="punto_id", referencedColumnName="id", nullable=false)
     */
    private $punto;
    
    /**
     * @ORM\ManyToOne(targetEntity="OpcionesVoto")
     * @ORM\JoinColumn(name="opcionVoto_id", referencedColumnName="id", nullable=false)
     */
    private $opcionVoto;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accion", inversedBy="votacion")
     * @ORM\JoinColumn(name="accion_id", referencedColumnName="id", nullable=false)
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
     * Set punto
     *
     * @param \Lugh\WebAppBundle\Entity\PuntoDia $punto
     * @return VotoPunto
     */
    public function setPunto(\Lugh\WebAppBundle\Entity\PuntoDia $punto = null)
    {
        $this->punto = $this->restrictionPunto($punto);

        return $this;
    }

    /**
     * Get punto
     *
     * @return \Lugh\WebAppBundle\Entity\PuntoDia 
     */
    public function getPunto()
    {
        return $this->punto;
    }

    /**
     * Set opcionVoto
     *
     * @param \Lugh\WebAppBundle\Entity\OpcionesVoto $opcionVoto
     * @return VotoPunto
     */
    public function setOpcionVoto(\Lugh\WebAppBundle\Entity\OpcionesVoto $opcionVoto = null)
    {
        $this->restrictionOpcionVoto($this->punto, $opcionVoto);
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
     * Set accion
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accion
     * @return VotoPunto
     */
    public function setAccion(\Lugh\WebAppBundle\Entity\Accion $accion = null)
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
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    private function restrictionPunto($punto)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->hasSubpunto($punto); 
        return $punto;
    }
    
    private function restrictionOpcionVoto($punto, $opcionVoto)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->restrictionOpcionVoto($punto, $opcionVoto); 
        return $punto;
    }
}
