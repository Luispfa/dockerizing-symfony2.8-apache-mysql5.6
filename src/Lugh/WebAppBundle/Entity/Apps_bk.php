<?php

//namespace Lugh\WebAppBundle\Entity;
//
//use Doctrine\ORM\Mapping as ORM;
//use JMS\Serializer\Annotation\Expose;
//use JMS\Serializer\Annotation\ExclusionPolicy;
//
///**
// * Auth
// *
// * @ORM\Table()
// * @ORM\Entity
// * @ExclusionPolicy("all")
// */
//class Apps
//{
//    /**
//     *
//     * @ORM\Column(name="id", type="string", length=36)
//     * @ORM\Id
//     * @ORM\GeneratedValue(strategy="UUID")
//     */
//    private $id;
//
//    /**
//     * @var boolean
//     *
//     * @ORM\Column(name="voto", type="boolean", options={"default":true})
//     * @Expose
//     */
//    private $voto;
//    
//    /**
//     * @var boolean
//     *
//     * @ORM\Column(name="foro", type="boolean", options={"default":true})
//     * @Expose
//     */
//    private $foro;
//    
//    /**
//     * @var boolean
//     *
//     * @ORM\Column(name="derecho", type="boolean", options={"default":true})
//     * @Expose
//     */
//    private $derecho;
//
//    /**
//     * @ORM\OneToOne(targetEntity="Accionista", mappedBy="apps")
//     */
//    private $accionista;
//
//    
//
//    /**
//     * Get id
//     *
//     * @return string 
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    /**
//     * Set voto
//     *
//     * @param boolean $voto
//     * @return Apps
//     */
//    public function setVoto($voto)
//    {
//        $this->voto = $voto;
//
//        return $this;
//    }
//
//    /**
//     * Get voto
//     *
//     * @return boolean 
//     */
//    public function getVoto()
//    {
//        return $this->voto;
//    }
//
//    /**
//     * Set foro
//     *
//     * @param boolean $foro
//     * @return Apps
//     */
//    public function setForo($foro)
//    {
//        $this->foro = $foro;
//
//        return $this;
//    }
//
//    /**
//     * Get foro
//     *
//     * @return boolean 
//     */
//    public function getForo()
//    {
//        return $this->foro;
//    }
//
//    /**
//     * Set derecho
//     *
//     * @param boolean $derecho
//     * @return Apps
//     */
//    public function setDerecho($derecho)
//    {
//        $this->derecho = $derecho;
//
//        return $this;
//    }
//
//    /**
//     * Get derecho
//     *
//     * @return boolean 
//     */
//    public function getDerecho()
//    {
//        return $this->derecho;
//    }
//
//    /**
//     * Set accionista
//     *
//     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
//     * @return Apps
//     */
//    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista = null)
//    {
//        $this->accionista = $accionista;
//
//        return $this;
//    }
//
//    /**
//     * Get accionista
//     *
//     * @return \Lugh\WebAppBundle\Entity\Accionista 
//     */
//    public function getAccionista()
//    {
//        return $this->accionista;
//    }
//}
