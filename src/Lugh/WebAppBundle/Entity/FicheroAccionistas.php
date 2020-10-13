<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * FicheroAccionistas
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="faccionistas_unique", columns={"name", "nif", "doc1", "doc2"})})
 * @ORM\Entity
 */
class FicheroAccionistas
{
    const nameClass = 'FicheroAccionistas';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nif", type="string", length=20, nullable=true)
     */
    protected $nif;
    
    /**
     * @var string
     *
     * @ORM\Column(name="doc1", type="string", length=255, nullable=true)
     */
    protected $doc1;
    
    /**
     * @var string
     *
     * @ORM\Column(name="doc2", type="string", length=255, nullable=true)
     */
    protected $doc2;

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
}
