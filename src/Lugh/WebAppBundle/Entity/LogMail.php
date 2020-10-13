<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Document
 *
 * @ORM\Entity(repositoryClass="Lugh\WebAppBundle\Repository\CustomRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class LogMail
{
    const nameClass = 'LogMail';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="uniqueid", type="string", length=36)
     */
    protected $uniqueid;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="mailfrom", cascade={"persist"})
     * @ORM\JoinColumn(name="userfrom_id", referencedColumnName="id", nullable=true)
     */
    private $userfrom;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="mailto", cascade={"persist"})
     * @ORM\JoinColumn(name="userdest_id", referencedColumnName="id", nullable=true)
     */
    private $userdest;
    
    /**
     * @var text
     *
     * @ORM\Column(name="body", type="text")
     * @Expose
     * @Groups({"Mail"})
     */
    private $body;
    
    /**
     * @var text
     *
     * @ORM\Column(name="notification", type="text", nullable=true)
     * @Expose
     * @Groups({"Notification"})
     */
    private $notification;
    
    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=250)
     * @Expose
     * @Groups({"Mail", "Subject"})
     */
    private $subject;
    
    /**
     * @var string
     *
     * @ORM\Column(name="mailfrom", type="string", length=250)
     * @Expose
     * @Groups({"Mail"})
     */
    private $mailfrom;
    
    /**
     * @var string
     *
     * @ORM\Column(name="mailto", type="string", length=250)
     * @Expose
     * @Groups({"Mail"})
     * 
     */
    private $mailto;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="wf", type="boolean")
     */
    private $wf;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="hide", type="boolean")
     */
    private $hide;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     * @Expose
     */
    private $dateTime;
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"UserFrom"}) 
     * @SerializedName("userfromname")
     */
    
    public function getUserFromName()
    {
        if($this->userfrom != null){
            return $this->userfrom->getEmail();
        }
        return $this->getMailfrom();
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"UserTo"}) 
     * @SerializedName("usertoname")
     */
    
    public function getUserToMail()
    {
        if($this->userdest != null){
            return $this->userdest->getUsername();
        }
        return $this->getMailto();
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
     * Set body
     *
     * @param string $body
     * @return LogMail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set userfrom
     *
     * @param \Lugh\WebAppBundle\Entity\User $userfrom
     * @return LogMail
     */
    public function setUserfrom(\Lugh\WebAppBundle\Entity\User $userfrom = null)
    {
        $this->userfrom = $userfrom;

        return $this;
    }

    /**
     * Get userfrom
     *
     * @return \Lugh\WebAppBundle\Entity\User 
     */
    public function getUserfrom()
    {
        return $this->userfrom;
    }

    /**
     * Set userdest
     *
     * @param \Lugh\WebAppBundle\Entity\User $userdest
     * @return LogMail
     */
    public function setUserdest(\Lugh\WebAppBundle\Entity\User $userdest = null)
    {
        $this->userdest = $userdest;

        return $this;
    }

    /**
     * Get userdest
     *
     * @return \Lugh\WebAppBundle\Entity\User 
     */
    public function getUserdest()
    {
        return $this->userdest;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return LogMail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set wf
     *
     * @param boolean $wf
     * @return LogMail
     */
    public function setWf($wf)
    {
        $this->wf = $wf;

        return $this;
    }

    /**
     * Get wf
     *
     * @return boolean 
     */
    public function getWf()
    {
        return $this->wf;
    }

    /**
     * Set mailfrom
     *
     * @param string $mailfrom
     * @return LogMail
     */
    public function setMailfrom($mailfrom)
    {
        $this->mailfrom = $mailfrom;

        return $this;
    }

    /**
     * Get mailfrom
     *
     * @return string 
     */
    public function getMailfrom()
    {
        return $this->mailfrom;
    }

    /**
     * Set mailto
     *
     * @param string $mailto
     * @return LogMail
     */
    public function setMailto($mailto)
    {
        $this->mailto = $mailto;

        return $this;
    }

    /**
     * Get mailto
     *
     * @return string 
     */
    public function getMailto()
    {
        return $this->mailto;
    }

    /**
     * Set notification
     *
     * @param string $notification
     * @return LogMail
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return string 
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return LogMail
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
     * Set uniqueid
     *
     * @param string $uniqueid
     * @return LogMail
     */
    public function setUniqueid($uniqueid)
    {
        $this->uniqueid = $uniqueid;

        return $this;
    }

    /**
     * Get uniqueid
     *
     * @return string 
     */
    public function getUniqueid()
    {
        return $this->uniqueid;
    }

    /**
     * Set hide
     *
     * @param boolean $hide
     * @return LogMail
     */
    public function setHide($hide)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Get hide
     *
     * @return boolean 
     */
    public function getHide()
    {
        return $this->hide;
    }
}
