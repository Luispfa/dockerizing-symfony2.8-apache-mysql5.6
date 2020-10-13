<?php

namespace Lugh\WebAppBundle\Entity;

use Lugh\WebAppBundle\Entity\Accionista;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Registro
 *
 * @ORM\Entity
 */
class Registro
{
    const nameClass = 'Registro';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="titulares", type="string", length=1000)
     */
    protected $titulares;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="numero", type="integer")
     */
    private $numero;
    
     /**
     * @var Accionista
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="registros", cascade={"persist"})
     * @ORM\JoinColumn(name="accionista_id", referencedColumnName="id", nullable=false)
     **/
    private $accionista;
    
    /**
     * @var string
     *
     * @ORM\Column(name="referencia", type="string", length=255)
     */
    protected $referencia;
    
    
   

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
     * Set name
     *
     * @param string $name
     * @return FicheroAccionistas
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
     * Set nif
     *
     * @param string $nif
     * @return FicheroAccionistas
     */
    public function setNif($nif)
    {
        $this->nif = $nif;

        return $this;
    }

    /**
     * Get nif
     *
     * @return string 
     */
    public function getNif()
    {
        return $this->nif;
    }

    /**
     * Set doc1
     *
     * @param string $doc1
     * @return FicheroAccionistas
     */
    public function setDoc1($doc1)
    {
        $this->doc1 = $doc1;

        return $this;
    }

    /**
     * Get doc1
     *
     * @return string 
     */
    public function getDoc1()
    {
        return $this->doc1;
    }

    /**
     * Set doc2
     *
     * @param string $doc2
     * @return FicheroAccionistas
     */
    public function setDoc2($doc2)
    {
        $this->doc2 = $doc2;

        return $this;
    }

    /**
     * Get doc2
     *
     * @return string 
     */
    public function getDoc2()
    {
        return $this->doc2;
    }

    /**
     * Set titulares
     *
     * @param string $titulares
     * @return Registro
     */
    public function setTitulares($titulares)
    {
        $this->titulares = $titulares;

        return $this;
    }

    /**
     * Get titulares
     *
     * @return string 
     */
    public function getTitulares()
    {
        return $this->titulares;
    }

    /**
     * Set numero
     *
     * @param integer $numero
     * @return Registro
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return integer 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return Registro
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista = null)
    {
        $this->accionista = $accionista;

        return $this;
    }

    /**
     * Get accionista
     *
     * @return \Lugh\WebAppBundle\Entity\Accionista 
     */
    public function getAccionista()
    {
        return $this->accionista;
    }

    /**
     * Set referencia
     *
     * @param string $referencia
     * @return Registro
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;

        return $this;
    }

    /**
     * Get referencia
     *
     * @return string 
     */
    public function getReferencia()
    {
        return $this->referencia;
    }
}
