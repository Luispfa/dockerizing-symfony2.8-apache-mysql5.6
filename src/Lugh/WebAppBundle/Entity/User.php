<?php

namespace Lugh\WebAppBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;


/**
 * @ORM\Entity
 * @ORM\Table()
 * 
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    const nameClass = 'User';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Accionista", mappedBy="user")
     * @Expose
     */
    private $accionista;
    
    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="owner", cascade={"persist", "remove"})
     */
    private $documents;
    
    /**
     * @ORM\OneToMany(targetEntity="Communique", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"Communiques"}) 
     */
    private $communiques;
    
    /**
     * @ORM\OneToMany(targetEntity="LogMail", mappedBy="userfrom", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"Mailfrom"}) 
     */
    private $mailfrom;
    
    /**
     * @ORM\OneToMany(targetEntity="LogMail", mappedBy="userdest", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"Mailto"}) 
     */
    private $mailto;
    
    /**
     * @var string
     *
     * @ORM\Column(name="cert", type="text", nullable=true)
     * 
     */
    private $cert;
    
    /**
     *
     * @Expose
     * @Groups({"Pass"}) 
     */
    private $pass;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=5, nullable=true)
     * @Expose
     */
    private $lang;
    
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
     * @SerializedName("isAdmin")
     */
    
    public function isAdmin()
    {
        return $this->getAccionista() == null;
    }

    /**
     * @VirtualProperty
     * @SerializedName("name")
     */
    public function name()
    {
        return $this->getAccionista() == null ? '' : $this->getAccionista()->getName();
    }

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    
    protected function getContainer()
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
     * Set accionista
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $accionista
     * @return User
     */
    public function setAccionista(\Lugh\WebAppBundle\Entity\Accionista $accionista = null)
    {
        $this->accionista = $accionista;

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
     * Add documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     * @return User
     */
    public function addDocument(\Lugh\WebAppBundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Lugh\WebAppBundle\Entity\Document $documents
     */
    public function removeDocument(\Lugh\WebAppBundle\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }
    
    public function setPlainPassword($password) {
        parent::setPlainPassword($password);
        $this->pass = $password;
    }
    
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Add mailfrom
     *
     * @param \Lugh\WebAppBundle\Entity\LogMail $mailfrom
     * @return User
     */
    public function addMailfrom(\Lugh\WebAppBundle\Entity\LogMail $mailfrom)
    {
        $this->mailfrom[] = $mailfrom;

        return $this;
    }

    /**
     * Remove mailfrom
     *
     * @param \Lugh\WebAppBundle\Entity\LogMail $mailfrom
     */
    public function removeMailfrom(\Lugh\WebAppBundle\Entity\LogMail $mailfrom)
    {
        $this->mailfrom->removeElement($mailfrom);
    }

    /**
     * Get mailfrom
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMailfrom()
    {
        return $this->mailfrom;
    }

    /**
     * Add mailto
     *
     * @param \Lugh\WebAppBundle\Entity\LogMail $mailto
     * @return User
     */
    public function addMailto(\Lugh\WebAppBundle\Entity\LogMail $mailto)
    {
        $this->mailto[] = $mailto;

        return $this;
    }

    /**
     * Remove mailto
     *
     * @param \Lugh\WebAppBundle\Entity\LogMail $mailto
     */
    public function removeMailto(\Lugh\WebAppBundle\Entity\LogMail $mailto)
    {
        $this->mailto->removeElement($mailto);
    }

    /**
     * Get mailto
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMailto()
    {
        return $this->mailto;
    }

    /**
     * Add communiques
     *
     * @param \Lugh\WebAppBundle\Entity\Communique $communiques
     * @return User
     */
    public function addCommunique(\Lugh\WebAppBundle\Entity\Communique $communiques)
    {
        $this->communiques[] = $communiques;

        return $this;
    }

    /**
     * Remove communiques
     *
     * @param \Lugh\WebAppBundle\Entity\Communique $communiques
     */
    public function removeCommunique(\Lugh\WebAppBundle\Entity\Communique $communiques)
    {
        $this->communiques->removeElement($communiques);
    }

    /**
     * Get communiques
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommuniques()
    {
        return $this->communiques;
    }

    /**
     * Set cert
     *
     * @param string $cert
     * @return User
     */
    public function setCert($cert)
    {
        $this->cert = $cert;

        return $this;
    }

    /**
     * Get cert
     *
     * @return string 
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return User
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
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
    
    public function setUsername($username)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        
        parent::setUsername($behavior->userExist($username));
        return $this;
    }
    
    public function setEmail($email)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        
        parent::setEmail($behavior->emailExist($email, $this->email));
        return $this;
    }
}
