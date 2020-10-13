<?php

namespace Lugh\DbConnectionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Auth
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Auth
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var string
     * 
     * @ORM\Column(name="host", type="string", length=255, unique=true, nullable=false)
     */
    private $host;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dbname", type="string", length=255)
     */
    private $dbname;

    /**
     * @var boolean
     *
     * @ORM\Column(name="voto", type="boolean")
     */
    private $voto;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="foro", type="boolean")
     */
    private $foro;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="derecho", type="boolean")
     */
    private $derecho;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="av", type="boolean")
     */
    private $av;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    
    /**
     * @ORM\ManyToOne(targetEntity="Template")
     */
    private $template;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="on_production_dates", type="boolean", options={"default":false})
     */
    private $onProductionDates;
    
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Auth
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return Auth
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set dbname
     *
     * @param string $dbname
     * @return Auth
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;

        return $this;
    }

    /**
     * Get dbname
     *
     * @return string 
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * Set voto
     *
     * @param boolean $voto
     * @return Auth
     */
    public function setVoto($voto)
    {
        $this->voto = $voto;

        return $this;
    }

    /**
     * Get voto
     *
     * @return boolean 
     */
    public function getVoto()
    {
        return $this->voto;
    }

    /**
     * Set foro
     *
     * @param boolean $foro
     * @return Auth
     */
    public function setForo($foro)
    {
        $this->foro = $foro;

        return $this;
    }

    /**
     * Get foro
     *
     * @return boolean 
     */
    public function getForo()
    {
        return $this->foro;
    }

    /**
     * Set derecho
     *
     * @param boolean $derecho
     * @return Auth
     */
    public function setDerecho($derecho)
    {
        $this->derecho = $derecho;

        return $this;
    }

    /**
     * Get derecho
     *
     * @return boolean 
     */
    public function getDerecho()
    {
        return $this->derecho;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Auth
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set template
     *
     * @param \Lugh\DbConnectionBundle\Entity\Template $template
     * @return Auth
     */
    public function setTemplate(\Lugh\DbConnectionBundle\Entity\Template $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Lugh\DbConnectionBundle\Entity\Template 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set av
     *
     * @param boolean $av
     * @return Auth
     */
    public function setAv($av)
    {
        $this->av = $av;

        return $this;
    }

    /**
     * Get av
     *
     * @return boolean 
     */
    public function getAv()
    {
        return $this->av;
    }

    /**
     * Set onProductionDates
     *
     * @param boolean $onProductionDates
     * @return Auth
     */
    public function setOnProductionDates($onProductionDates)
    {
        $this->onProductionDates = $onProductionDates;

        return $this;
    }

    /**
     * Get onProductionDates
     *
     * @return boolean 
     */
    public function getOnProductionDates()
    {
        return $this->onProductionDates;
    }
}
