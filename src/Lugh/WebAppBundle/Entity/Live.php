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
class Live
{
    
    const nameClass = 'Live';
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
     * @var string
     *
     * @ORM\Column(name="event_id", type="integer")
     * @Expose
     */
    private $eventId;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="integer")
     * @Expose
     */
    private $sessionId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="session_name", type="string", nullable=true)
     * @Expose
     */
    private $sessionName;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="session_start_datetime", type="datetime")
     * @Expose
     */
    private $sessionStartDatetime;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="session_finish_datetime", type="datetime")
     * @Expose
     */
    private $sessionFinishDatetime;
    
    /**
     * @var string
     *
     * @ORM\Column(name="app_version", type="string", nullable=true)
     * @Expose
     */
    private $appVersion;
    
    /**
     * @var string
     *
     * @ORM\Column(name="session_live_status", type="string", nullable=true)
     * @Expose
     */
    private $sessionLiveStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(name="session_od_status", type="string", nullable=true)
     * @Expose
     */
    private $sessionOdStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     * @Expose
     */
    private $url;
    
    /**
     * @ORM\OneToMany(targetEntity="AppAVLive", mappedBy="lives")
     **/
    private $avs;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default":false})
     * @Expose
     */
    private $enabled = false;
    
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
     * Set eventId
     *
     * @param integer $eventId
     * @return Live
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer 
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set sessionId
     *
     * @param integer $sessionId
     * @return Live
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return integer 
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set sessionName
     *
     * @param string $sessionName
     * @return Live
     */
    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;

        return $this;
    }

    /**
     * Get sessionName
     *
     * @return string 
     */
    public function getSessionName()
    {
        return $this->sessionName;
    }

    /**
     * Set sessionStartDatetime
     *
     * @param \DateTime $sessionStartDatetime
     * @return Live
     */
    public function setSessionStartDatetime($sessionStartDatetime)
    {
        $this->sessionStartDatetime = $sessionStartDatetime;

        return $this;
    }

    /**
     * Get sessionStartDatetime
     *
     * @return \DateTime 
     */
    public function getSessionStartDatetime()
    {
        return $this->sessionStartDatetime;
    }

    /**
     * Set sessionFinishDatetime
     *
     * @param \DateTime $sessionFinishDatetime
     * @return Live
     */
    public function setSessionFinishDatetime($sessionFinishDatetime)
    {
        $this->sessionFinishDatetime = $sessionFinishDatetime;

        return $this;
    }

    /**
     * Get sessionFinishDatetime
     *
     * @return \DateTime 
     */
    public function getSessionFinishDatetime()
    {
        return $this->sessionFinishDatetime;
    }

    /**
     * Set appVersion
     *
     * @param string $appVersion
     * @return Live
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    /**
     * Get appVersion
     *
     * @return string 
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * Set sessionLiveStatus
     *
     * @param string $sessionLiveStatus
     * @return Live
     */
    public function setSessionLiveStatus($sessionLiveStatus)
    {
        $this->sessionLiveStatus = $sessionLiveStatus;

        return $this;
    }

    /**
     * Get sessionLiveStatus
     *
     * @return string 
     */
    public function getSessionLiveStatus()
    {
        return $this->sessionLiveStatus;
    }

    /**
     * Set sessionOdStatus
     *
     * @param string $sessionOdStatus
     * @return Live
     */
    public function setSessionOdStatus($sessionOdStatus)
    {
        $this->sessionOdStatus = $sessionOdStatus;

        return $this;
    }

    /**
     * Get sessionOdStatus
     *
     * @return string 
     */
    public function getSessionOdStatus()
    {
        return $this->sessionOdStatus;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->avs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Live
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Live
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
     * Add avs
     *
     * @param \Lugh\WebAppBundle\Entity\AppAVLive $avs
     * @return Live
     */
    public function addAv(\Lugh\WebAppBundle\Entity\AppAVLive $avs)
    {
        $this->avs[] = $avs;

        return $this;
    }

    /**
     * Remove avs
     *
     * @param \Lugh\WebAppBundle\Entity\AppAVLive $avs
     */
    public function removeAv(\Lugh\WebAppBundle\Entity\AppAVLive $avs)
    {
        $this->avs->removeElement($avs);
    }

    /**
     * Get avs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAvs()
    {
        return $this->avs;
    }
}
