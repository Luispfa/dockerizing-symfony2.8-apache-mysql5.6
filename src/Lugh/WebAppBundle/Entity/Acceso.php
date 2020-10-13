<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;

/**
 * Acceso
 *
 * @ORM\Entity  @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="integer")
 * @ORM\DiscriminatorMap({0 = "AccesoVoto", 1 = "AccesoForo", 2 = "AccesoDerecho", 3 = "AccesoAV"})
 * 
 * @ExclusionPolicy("all")
 */
abstract class Acceso {
    
    const accesoVoto       = 0;
    const accesoForo       = 1;
    const accesoDerecho    = 2;
    const accesoAv         = 3;
    
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="acceso", cascade={"persist"})
     * @ORM\JoinColumn(name="accionista_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $accionista;
    
    /**
     * @ORM\OneToOne(targetEntity="Acceso")
     * @ORM\JoinColumn()
     */
    private $accesoAnterior;
    
    /**
     * @ORM\OneToOne(targetEntity="Acceso", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $accesoPosterior;
    
    /**
     * @var string
     *
     * @ORM\Column(name="movFileTagged", type="string", length=255, nullable=true)
     * @Expose
     */
    private $movFileTagged;
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }

    abstract function getAccesoClass();

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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Acceso
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

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
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return Acceso
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista)
    {
        //$accionista->setApp($this); /* @TODO: Apps */
        $this->accionista = $accionista;
        $this->setAccAnterior();
        $this->setAccPosterior();

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
     * Constructor
     */
    public function __construct()
    {
        $this->setDateTime(new \DateTime());
    }
    
    /**
     * Set accesoAnterior
     *
     * @param \Lugh\WebAppBundle\Entity\Acceso $accesoAnterior
     * @return Acceso
     */
    public function setAccesoAnterior(\Lugh\WebAppBundle\Entity\Acceso $accesoAnterior = null)
    {
        $this->accesoAnterior = $accesoAnterior;

        return $this;
    }

    /**
     * Get accesoAnterior
     *
     * @return \Lugh\WebAppBundle\Entity\Acceso 
     */
    public function getAccesoAnterior()
    {
        return $this->accesoAnterior;
    }
    
    abstract function findAccesoAnterior();
    
    /*public function findAccesoAnterior()
    {
        if (is_array($this->getAccionista()->getAllAccesoForFind()))
        {
            $accesos = $this->getAccionista()->getAllAccesoForFind();
            return end($accesos);
        }
        return $this->getAccionista()->getAllAccesoForFind()->last();
    }*/
    
    private function setAccAnterior() {
        $lastItem = $this->findAccesoAnterior();
        if ($lastItem)
        {
            $this->setAccesoAnterior($this->restrictions($lastItem));
        }
        else {
            $this->restrictions(null);
        }
    }
    
    private function setAccPosterior() {
        $lastItem = $this->findAccesoAnterior();
        if ($lastItem)
        {
            $lastItem->setAccesoPosterior($this);
        }
    }
    
    /**
     * Set accesoPosterior
     *
     * @param \Lugh\WebAppBundle\Entity\Acceso $accesoPosterior
     * @return Acceso
     */
    public function setAccesoPosterior(\Lugh\WebAppBundle\Entity\Acceso $accesoPosterior = null)
    {
        $this->accesoPosterior = $accesoPosterior;

        return $this;
    }

    /**
     * Get accesoPosterior
     *
     * @return \Lugh\WebAppBundle\Entity\Acceso 
     */
    public function getAccesoPosterior()
    {
        return $this->accesoPosterior;
    }

    private function restrictions($lastItem)
    {
        //$behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        //$behavior->annulationPermitted($this,$lastItem);
        //$behavior->putPendingAppAv($this);
        return $lastItem;    
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
