<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Live
 *
 * @ORM\Table()
 * @ORM\Entity
 * 
 * @ExclusionPolicy("all")
 */
class AppAVLive
{
    
    const nameClass = 'AppAVLive';
    const appClass  = 'Av';
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
     * @ORM\ManyToOne(targetEntity="AppAV", inversedBy="lives")
     */

    private $av;

    /**
     * @ORM\ManyToOne(targetEntity="Live", inversedBy="avs")
     */
    private $live;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $enabled = false;
    

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
     * Set enabled
     *
     * @param boolean $enabled
     * @return AppAVLive
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }


    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set av
     *
     * @param \Lugh\WebAppBundle\Entity\AppAV $av
     * @return AppAVLive
     */
    public function setAv(\Lugh\WebAppBundle\Entity\AppAV $av = null)
    {
        $this->av = $av;

        return $this;
    }

    /**
     * Get av
     *
     * @return \Lugh\WebAppBundle\Entity\AppAV 
     */
    public function getAv()
    {
        return $this->av;
    }

    /**
     * Set live
     *
     * @param \Lugh\WebAppBundle\Entity\Live $live
     * @return AppAVLive
     */
    public function setLive(\Lugh\WebAppBundle\Entity\Live $live = null)
    {
        $this->live = $live;

        return $this;
    }

    /**
     * Get live
     *
     * @return \Lugh\WebAppBundle\Entity\Live 
     */
    public function getLive()
    {
        return $this->live;
    }
}
