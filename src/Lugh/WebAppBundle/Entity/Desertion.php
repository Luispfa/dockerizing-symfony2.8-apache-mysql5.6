<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * Desertion
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Desertion
{
    const nameClass = 'Desertion';
    const appClass  = 'AV';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Accionista", inversedBy="desertion", cascade={"persist"})
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $autor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    /**
     * @var string
     *
     * @ORM\Column(name="movFileTagged", type="string", length=255, nullable=true)
     * @Expose
     */
    private $movFileTagged;
    

    public function __construct() {
        
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
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $autor
     * @return Thread
     */
    public function setAutor(\Lugh\WebAppBundle\Entity\Accionista $autor)
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return \Lugh\WebAppBundle\Entity\Accionista 
     */
    public function getAutor()
    {
        return $this->autor;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Item
     */
    public function setDateTime($dateTime)
    {
        if($this->dateTime == null){
            $this->dateTime = $dateTime;
        }
        
        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime 
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
    
    /**
     * Set movFileTagged
     *
     * @param string $movFileTagged
     * @return Accion
     */
    public function setMovFileTagged($movFileTagged)
    {
        $this->movFileTagged = $movFileTagged;

        return $this;
    }

    /**
     * Get movFileTagged
     *
     * @return string 
     */
    public function getMovFileTagged()
    {
        return $this->movFileTagged;
    }
}
