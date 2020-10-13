<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
/**
 * Accion
 *
 * @ORM\Entity  @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="integer")
 * @ORM\DiscriminatorMap({0 = "Voto", 1 = "Delegacion", 2 = "Av", 95 = "AccionRechazada", 99 = "Anulacion", 100 = "AnulacionAv"})
 * 
 * @ExclusionPolicy("all")
 */
abstract class Accion {
    
    const nameClass = 'Accion';
    const appClass  = 'Voto';
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateTimeCreate", type="datetime")
     * @Expose
     */
    private $dateTimeCreate;


    /**
     * @ORM\OneToMany(targetEntity="VotoAbsAdicional", mappedBy="accion", cascade={"persist", "remove"})
     * @Type("array")
     * @Expose
     */
    private $votoAbsAdicional;
    
    /**
     * @ORM\OneToMany(targetEntity="VotoPunto", mappedBy="accion", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"Votacion"}) 
     */
    private $votacion;
    
    /**
     * @ORM\OneToMany(targetEntity="VotoSerie", mappedBy="accion", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"VotacionSerie"}) 
     */
    private $votacionSerie;
    
    /**
     * @ORM\ManyToOne(targetEntity="Accionista", inversedBy="accion", cascade={ "remove"})
     * @Expose
     */
    private $accionista;
    
    /**
     * @ORM\OneToOne(targetEntity="Accion")
     * @ORM\JoinColumn()
     */
    private $accionAnterior;
    
    /**
     * @ORM\OneToOne(targetEntity="Accion", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $accionPosterior;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="sharesNum", type="integer", options={"default":0})
     * @Expose
     */
    private $sharesNum = 0;
    
    /**
     * @var string
     *
     * @ORM\Column(name="movFileTagged", type="string", length=255, nullable=true)
     * @Expose
     */
    private $movFileTagged;

    /**
     * 
     * @VirtualProperty
     * @Groups({"VotoSerieDecrypt"}) 
     * @SerializedName("votacion_serie_decrypt")
     */
    
    public function getVotoSerieDecrypt()
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->getVotoSerieDecrypt($this->getVotacionSerie());
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"VirtualMail"}) 
     * @SerializedName("votohtml")
     */
    
    public function getVotoHtml()
    {
        $str = '<br/>';
        if($this->getVotacion() != null){
            foreach ($this->getVotacion() as $voto) {
                $str .= $voto->getPunto()->getNumPunto() . 
                        ': ' .
                        $voto->getOpcionVoto()->getNombre() .
                        '<br/>';
            }
        }
        return $str;
    }
    
    
    private function setAccAnterior() {
        $lastItem = $this->findAccionAnterior();
        if ($lastItem)
        {
            $this->setAccionAnterior($this->restrictions($lastItem));
        }
        else {
            $this->restrictions(null);
        }
    }
    
    private function setAccPosterior() {
        $lastItem = $this->findAccionAnterior();
        if ($lastItem)
        {
            $lastItem->setAccionPosterior($this);
        }
    }
    
    private function restrictions($lastItem)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->annulationPermitted($this,$lastItem);
        //$behavior->putPendingAppAv($this);
        return $lastItem;    
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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Accion
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
     * Constructor
     */
    public function __construct()
    {
        $this->votacion = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add votacion
     *
     * @param \Lugh\WebAppBundle\Entity\VotoPunto $votacion
     * @return Accion
     */
    public function addVotacion(\Lugh\WebAppBundle\Entity\VotoPunto $votacion)
    {
        $votacion->setAccion($this);
        $this->votacion[] = $this->restrictionMaxVotos($votacion);

        return $this;
    }

    /**
     * Remove votacion
     *
     * @param \Lugh\WebAppBundle\Entity\VotoPunto $votacion
     */
    public function removeVotacion(\Lugh\WebAppBundle\Entity\VotoPunto $votacion)
    {
        $this->votacion->removeElement($votacion);
    }

    /**
     * Get votacion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotacion()
    {
        return $this->votacion;
    }

    /**
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return Accion
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista = null)
    {
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
     * Set accionAnterior
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accionAnterior
     * @return Anulacion
     */
    public function setAccionAnterior(\Lugh\WebAppBundle\Entity\Accion $accionAnterior = null)
    {
        $this->accionAnterior = $accionAnterior;

        return $this;
    }

    /**
     * Get accionAnterior
     *
     * @return \Lugh\WebAppBundle\Entity\Accion 
     */
    public function getAccionAnterior()
    {
        return $this->accionAnterior;
    }
    
    public function findAccionAnterior()
    {
        //die(var_dump($this->getAccionista()->getAllAccionForFind()));
        if (is_array($this->getAccionista()->getAllAccionForFind()))
        {
            $actions = $this->getAccionista()->getAllAccionForFind();
            return end($actions);
        }
        return $this->getAccionista()->getAllAccionForFind()->last();
    }
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    private function restrictionMaxVotos($voto)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->maxVotos($this->votacion, $voto);  
        return $voto;
    }

    /**
     * Set sharesNum
     *
     * @param integer $sharesNum
     * @return Accion
     */
    public function setSharesNum($sharesNum)
    {
        $this->sharesNum = $sharesNum;

        return $this;
    }

    /**
     * Get sharesNum
     *
     * @return integer 
     */
    public function getSharesNum()
    {
        return $this->sharesNum;
    }

    /**
     * Add votacionSerie
     *
     * @param \Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie
     * @return Accion
     */
    public function addVotacionSerie(\Lugh\WebAppBundle\Entity\VotoSerie $votacionSerie)
    {
        $votacionSerie->setAccion($this);
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
    * Add Voting
    * @param array $votacion  <p>
    * array (
     *          'punto_id',
     *          'opcionVoto_id'
     *      )
    * </p>
    */
    public function addVotos($votacion)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->addVotos($this, $votacion); 
    }

    /**
     * Set accionPosterior
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accionPosterior
     * @return Accion
     */
    public function setAccionPosterior(\Lugh\WebAppBundle\Entity\Accion $accionPosterior = null)
    {
        $this->accionPosterior = $accionPosterior;

        return $this;
    }

    /**
     * Get accionPosterior
     *
     * @return \Lugh\WebAppBundle\Entity\Accion 
     */
    public function getAccionPosterior()
    {
        return $this->accionPosterior;
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

    /**
     * Set dateTimeCreate
     *
     * @param \DateTime $dateTimeCreate
     * @return Accion
     */
    public function setDateTimeCreate($dateTimeCreate)
    {
        $this->dateTimeCreate = $dateTimeCreate;

        return $this;
    }

    /**
     * Get dateTimeCreate
     *
     * @return \DateTime 
     */
    public function getDateTimeCreate()
    {
        return $this->dateTimeCreate;
    }


    /**
     * Add votoAbsAdicional
     *
     * @param \Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional
     * @return Accion
     */
    public function addVotoAbsAdicional(\Lugh\WebAppBundle\Entity\VotoAbsAdicional $votoAbsAdicional)
    {
        $votoAbsAdicional->setAccion($this);
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
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"VirtualMail"}) 
     * @SerializedName("shares")
     */
    
    public function getSharesString()
    {
        return strval($this->sharesNum);
    }
}
