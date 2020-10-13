<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Parametros
 *
 * @ORM\Table()
 * @ORM\Entity
 * 
 * @ExclusionPolicy("all")
 */
class Parametros
{
    
    const nameClass = 'Parametros';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", length=36))
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key_param", type="string", length=255)
     * @Expose
     */
    private $key_param;

    /**
     * @var string
     *
     * @ORM\Column(name="value_param", type="text", nullable=true)
     * @Expose
     */
    private $value_param;
    
    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     * @Expose
     */
    private $observaciones;
    
    private function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
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
     * Set observaciones
     *
     * @param string $observaciones
     * @return Parametros
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set key_param
     *
     * @param string $keyParam
     * @return Parametros
     */
    public function setKeyParam($keyParam)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();       
        $this->key_param = $behavior->noContent($keyParam);

        return $this;
    }

    /**
     * Get key_param
     *
     * @return string 
     */
    public function getKeyParam()
    {
        return $this->key_param;
    }

    /**
     * Set value_param
     *
     * @param string $valueParam
     * @return Parametros
     */
    public function setValueParam($valueParam)
    {
        $this->value_param = $valueParam;

        return $this;
    }

    /**
     * Get value_param
     *
     * @return string 
     */
    public function getValueParam()
    {
        return $this->value_param;
    }
}
