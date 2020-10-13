<?php

namespace Lugh\WebAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lugh\WebAppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\MaxDepth;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * Accionista
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * 
 * @ExclusionPolicy("all")
 */
class Accionista /* @TODO: Apps */
{
    const nameClass = 'Accionista';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="accionista", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * 
     * @Expose
     */
    protected $user;
  
    /**
     * @ORM\OneToMany(targetEntity="App", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"App"})
     */
    protected $app;
    
    /**
     * @ORM\OneToMany(targetEntity="Acceso", mappedBy="accionista", cascade={"persist", "remove"})
     * @ORM\OrderBy({"dateTime" = "ASC"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAccesos")
     * @Groups({"Acceso"})
     */
    protected $accesos;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Expose
     */
    protected $name;

    /**
     * @var string
     * 
     * @ORM\Column(name="telephone", type="string", length=20, nullable=true)
     * @Groups({"Personal", "VarMail"}) 
     * @Expose
     */
    private $telephone;
    
    /**
     * @ORM\OneToMany(targetEntity="Initiative", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getInitiatives")
     * @Groups({"Initiatives"}) 
     */
    private $initiatives;
    
    /**
     * @ORM\OneToMany(targetEntity="Offer", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getOffers")
     * @Groups({"Offers"})
     */
    private $offers;
    
    /**
     * @ORM\OneToMany(targetEntity="Request", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getRequests")
     * @Groups({"Requests"})
     */
    private $requests;
    
    /**
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getProposals")
     * @Groups({"Proposals"})
     */
    private $proposals;
    
    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getThreads")
     * @Groups({"Threads"})
     */
    private $threads;
    
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getQuestions")
     * @Groups({"Questions"})
     */
    private $questions;
    
    /**
     * @ORM\OneToOne(targetEntity="Desertion", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Groups({"Desertion"})
     */
    private $desertion;
    
    /**
     * @ORM\OneToMany(targetEntity="Registro", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getRegistros")
     * @Groups({"Registro"})
     */
    private $registros;
    
    /**
     * @ORM\OneToMany(targetEntity="AdhesionInitiative", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAdhesionsInitiatives")
     * @Groups({"AdhesionsInitiatives"})
     */
    private $adhesionsInitiatives;
    
    /**
     * @ORM\OneToMany(targetEntity="AdhesionOffer", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAdhesionsOffers")
     * @Groups({"AdhesionsOffers"})
     */
    private $adhesionsOffers;
    
    /**
     * @ORM\OneToMany(targetEntity="AdhesionRequest", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAdhesionsRequests")
     * @Groups({"AdhesionsRequests"})
     */
    private $adhesionsRequests;
    
    /**
     * @ORM\OneToMany(targetEntity="AdhesionProposal", mappedBy="accionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAdhesionsProposals")
     * @Groups({"AdhesionsProposals"})
     */
    private $adhesionsProposals;
    
    /**
     * @ORM\OneToMany(targetEntity="Accion", mappedBy="accionista", cascade={"persist", "remove"})
     * @ORM\OrderBy({"dateTime" = "ASC"})
     * @Expose
     * @Type("array")
     * @Accessor(getter="getAccion")
     * @Groups({"Accion"})
     */
    private $accion;
    
    /**
     * @ORM\OneToOne(targetEntity="ItemAccionista", mappedBy="autor", cascade={"persist", "remove"})
     * @Expose
     * @Groups({"ItemAccionista"})
     */
    private $itemAccionista;

    /**
     * @var string
     *
     * @ORM\Column(name="representedBy", type="string", length=255)
     * @Expose
     */
    private $representedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="documentNum", type="string", length=40) 
     * @Expose
     * @Groups({"Personal"})
     */
    private $documentNum;

    /**
     * @var string
     *
     * @ORM\Column(name="documentType", type="string", length=10)
     * @Groups({"Personal"}) 
     * @Expose
     */
    private $documentType;

    /**
     * @var integer
     *
     * @ORM\Column(name="sharesNum", type="integer")
     * @Expose
     */
    private $sharesNum;
    
    /**
     * @var FicheroAccionistas
     * @ORM\OneToOne(targetEntity="FicheroAccionistas", fetch="EAGER", orphanRemoval=true)
     * @ORM\JoinColumn(name="ficheroaccionista_id", referencedColumnName="id")
     **/
    private $ficheroAccionista = null;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="acreditado", type="boolean", options={"default":0})
     * @Expose
     */
    private $acreditado;
    
    /**
     * @var string
     *
     * @ORM\Column(name="received_json", type="string", length=1000, nullable=true)
     * @Expose
     */
    private $receivedJson;
    
    /**
     * @var string
     *
     * @ORM\Column(name="valid_json", type="string", length=1000, nullable=true)
     * @Expose
     */
    private $validJson;
    
    
    /** 
     * @ORM\PrePersist
     */
    public function doStateOnPrePersist()
    {
        if ($this->getItemAccionista()==null)
        {
            throw new Exception("Accionista should have an ItemAccionista");
        }
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
     * Set name
     *
     * @param string $name
     * @return Accionista
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set user
     *
     * @param \Lugh\WebAppBundle\Entity\User $user
     * @return Accionista
     */
    public function setUser(\Lugh\WebAppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Lugh\WebAppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initiatives = new \Doctrine\Common\Collections\ArrayCollection();
        $this->offers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->proposals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->threads = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adhesionsInitiatives = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adhesionsOffers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adhesionsRequests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adhesionsProposals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->acreditado = false;
    }

    /**
     * Add initiatives
     *
     * @param \Lugh\WebAppBundle\Entity\Initiative $initiatives
     * @return Accionista
     */
    public function addInitiative(\Lugh\WebAppBundle\Entity\Initiative $initiatives)
    {
        $initiatives->setAutor($this);
        $this->initiatives[] = $initiatives;

        return $this;
    }

    /**
     * Remove initiatives
     *
     * @param \Lugh\WebAppBundle\Entity\Initiative $initiatives
     */
    public function removeInitiative(\Lugh\WebAppBundle\Entity\Initiative $initiatives)
    {
        $this->initiatives->removeElement($initiatives);
    }

    /**
     * Get initiatives
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInitiatives()
    {
        return $this->restrictions($this->initiatives);
    }

    /**
     * Add offers
     *
     * @param \Lugh\WebAppBundle\Entity\Offer $offers
     * @return Accionista
     */
    public function addOffer(\Lugh\WebAppBundle\Entity\Offer $offers)
    {
        $offers->setAutor($this);
        $this->offers[] = $offers;

        return $this;
    }

    /**
     * Remove offers
     *
     * @param \Lugh\WebAppBundle\Entity\Offer $offers
     */
    public function removeOffer(\Lugh\WebAppBundle\Entity\Offer $offers)
    {
        $this->offers->removeElement($offers);
    }

    /**
     * Get offers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOffers()
    {
        return $this->restrictions($this->offers);
    }

    /**
     * Add requests
     *
     * @param \Lugh\WebAppBundle\Entity\Request $requests
     * @return Accionista
     */
    public function addRequest(\Lugh\WebAppBundle\Entity\Request $requests)
    {
        $requests->setAutor($this);
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \Lugh\WebAppBundle\Entity\Request $requests
     */
    public function removeRequest(\Lugh\WebAppBundle\Entity\Request $requests)
    {
        $this->requests->removeElement($requests);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequests()
    {
        return $this->restrictions($this->requests);
    }

    /**
     * Add proposals
     *
     * @param \Lugh\WebAppBundle\Entity\Proposal $proposals
     * @return Accionista
     */
    public function addProposal(\Lugh\WebAppBundle\Entity\Proposal $proposals)
    {
        $proposals->setAutor($this);
        $this->proposals[] = $proposals;

        return $this;
    }

    /**
     * Remove proposals
     *
     * @param \Lugh\WebAppBundle\Entity\Proposal $proposals
     */
    public function removeProposal(\Lugh\WebAppBundle\Entity\Proposal $proposals)
    {
        $this->proposals->removeElement($proposals);
    }

    /**
     * Get proposals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProposals()
    {
        return $this->restrictions($this->proposals);
    }

    /**
     * Add threads
     *
     * @param \Lugh\WebAppBundle\Entity\Thread $threads
     * @return Accionista
     */
    public function addThread(\Lugh\WebAppBundle\Entity\Thread $threads)
    {
        $threads->setAutor($this);
        $this->threads[] = $threads;

        return $this;
    }

    /**
     * Remove threads
     *
     * @param \Lugh\WebAppBundle\Entity\Thread $threads
     */
    public function removeThread(\Lugh\WebAppBundle\Entity\Thread $threads)
    {
        $this->threads->removeElement($threads);
    }

    /**
     * Get threads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getThreads()
    {
        return $this->restrictions($this->threads);
    }
    
    /**
     * Add questions
     *
     * @param \Lugh\WebAppBundle\Entity\Question $questions
     * @return Accionista
     */
    public function addQuestion(\Lugh\WebAppBundle\Entity\Question $questions)
    {
        $questions->setAutor($this);
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Lugh\WebAppBundle\Entity\Question $questions
     */
    public function removeQuestion(\Lugh\WebAppBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->restrictions($this->questions);
    }

    /**
     * Add adhesionsInitiatives
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesionsInitiatives
     * @return Accionista
     */
    public function addAdhesionsInitiative(\Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesionsInitiatives)
    {
        $adhesionsInitiatives->setAccionista($this);
        $this->adhesionsInitiatives[] = $adhesionsInitiatives;

        return $this;
    }

    /**
     * Remove adhesionsInitiatives
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesionsInitiatives
     */
    public function removeAdhesionsInitiative(\Lugh\WebAppBundle\Entity\AdhesionInitiative $adhesionsInitiatives)
    {
        $this->adhesionsInitiatives->removeElement($adhesionsInitiatives);
    }

    /**
     * Get adhesionsInitiatives
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdhesionsInitiatives()
    {
        return $this->restrictions($this->adhesionsInitiatives);
    }

    /**
     * Add adhesionsOffers
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionOffer $adhesionsOffers
     * @return Accionista
     */
    public function addAdhesionsOffer(\Lugh\WebAppBundle\Entity\AdhesionOffer $adhesionsOffers)
    {
        $adhesionsOffers->setAccionista($this);
        $this->adhesionsOffers[] = $adhesionsOffers;

        return $this;
    }

    /**
     * Remove adhesionsOffers
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionOffer $adhesionsOffers
     */
    public function removeAdhesionsOffer(\Lugh\WebAppBundle\Entity\AdhesionOffer $adhesionsOffers)
    {
        $this->adhesionsOffers->removeElement($adhesionsOffers);
    }

    /**
     * Get adhesionsOffers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdhesionsOffers()
    {
        return $this->restrictions($this->adhesionsOffers);
    }

    /**
     * Add adhesionsRequests
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionRequest $adhesionsRequests
     * @return Accionista
     */
    public function addAdhesionsRequest(\Lugh\WebAppBundle\Entity\AdhesionRequest $adhesionsRequests)
    {
        $adhesionsRequests->setAccionista($this);
        $this->adhesionsRequests[] = $adhesionsRequests;

        return $this;
    }

    /**
     * Remove adhesionsRequests
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionRequest $adhesionsRequests
     */
    public function removeAdhesionsRequest(\Lugh\WebAppBundle\Entity\AdhesionRequest $adhesionsRequests)
    {
        $this->adhesionsRequests->removeElement($adhesionsRequests);
    }

    /**
     * Get adhesionsRequests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdhesionsRequests()
    {
        return $this->restrictions($this->adhesionsRequests);
    }

    /**
     * Add adhesionsProposals
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionProposal $adhesionsProposals
     * @return Accionista
     */
    public function addAdhesionsProposal(\Lugh\WebAppBundle\Entity\AdhesionProposal $adhesionsProposals)
    {
        $adhesionsProposals->setAccionista($this);
        $this->adhesionsProposals[] = $adhesionsProposals;

        return $this;
    }

    /**
     * Remove adhesionsProposals
     *
     * @param \Lugh\WebAppBundle\Entity\AdhesionProposal $adhesionsProposals
     */
    public function removeAdhesionsProposal(\Lugh\WebAppBundle\Entity\AdhesionProposal $adhesionsProposals)
    {
        $this->adhesionsProposals->removeElement($adhesionsProposals);
    }

    /**
     * Get adhesionsProposals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdhesionsProposals()
    {
        return $this->restrictions($this->adhesionsProposals);
    }

    /**
     * Add accion
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accion
     * @return Accionista
     */
    public function addAccion(\Lugh\WebAppBundle\Entity\Accion $accion)
    {
        $accion->setAccionista($this);
        $this->accion[] = $accion;

        return $this;
    }

    /**
     * Remove accion
     *
     * @param \Lugh\WebAppBundle\Entity\Accion $accion
     */
    public function removeAccion(\Lugh\WebAppBundle\Entity\Accion $accion)
    {
        $this->accion->removeElement($accion);
    }

    /**
     * Get accion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccion()
    {
        return $this->restrictions($this->accion);
    }
    
    /**
     * Get accion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAllAccionForFind()
    {
        return $this->accion;
    }

    /**
     * Set itemAccionista
     *
     * @param \Lugh\WebAppBundle\Entity\ItemAccionista $itemAccionista
     * @return Accionista
     */
    public function setItemAccionista(\Lugh\WebAppBundle\Entity\ItemAccionista $itemAccionista = null)
    {
        //$itemAccionista->setAutor($this);
        $this->itemAccionista = $itemAccionista;
        //$itemAccionista->pendiente();
        //$this->itemAccionista->setAutor($this);
        return $this;
    }

    /**
     * Get itemAccionista
     *
     * @return \Lugh\WebAppBundle\Entity\ItemAccionista 
     */
    public function getItemAccionista()
    {
        return $this->itemAccionista;
    }
    
    public function pendiente($comments = null){
        $this->getItemAccionista()->pendiente($comments);
        return $this;
    }
    public function publica($comments = null){
        $this->getItemAccionista()->publica($comments);
        return $this;
    }
    public function retorna($comments = null){
        $this->getItemAccionista()->retorna($comments);
        return $this;
    }
    public function rechaza($comments = null){
        $this->getItemAccionista()->rechaza($comments);
        return $this;
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"Documents"}) 
     * @SerializedName("documents")
     */
    
    public function getDocumentsProfile()
    {
        if($this->user->getDocuments() != null){
            $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("communique", null))
            ->andWhere(Criteria::expr()->eq("message", null));        
            
            return $this->user->getDocuments()->matching($criteria);
        }
        return $this->user->getDocuments();
    }
    
    /**
     * 
     * @VirtualProperty
     * @SerializedName("is_user_cert")
     */
    
    public function isUserCert()
    {
        return ($this->getUser()->getCert() != null && $this->getUser()->getCert() != '');
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"LastAccion"}) 
     * @SerializedName("lastAccion")
     */
    
    public function getLastAccion()
    {
        if($this->accion != null){
            $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("accionPosterior", null)); 
            
            return $this->accion->matching($criteria);
        }
        return array();
    }

    /**
     * Set representedBy
     *
     * @param string $representedBy
     * @return Accionista
     */
    public function setRepresentedBy($representedBy)
    {
        $this->representedBy = $representedBy;

        return $this;
    }

    /**
     * Get representedBy
     *
     * @return string 
     */
    public function getRepresentedBy()
    {
        return $this->representedBy;
    }

    /**
     * Set documentNum
     *
     * @param string $documentNum
     * @return Accionista
     */
    public function setDocumentNum($documentNum)
    {
        //$this->documentNum = $documentNum;
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        
        $this->documentNum = $behavior->documentNumExist($this->getDocumentNum(), $documentNum);

        return $this;
    }

    /**
     * Get documentNum
     *
     * @return string 
     */
    public function getDocumentNum()
    {
        return $this->documentNum;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return Accionista
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string 
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set sharesNum
     *
     * @param integer $sharesNum
     * @return Accionista
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
    
    private function restrictions($itemCollection, $state = 'get')
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->filterItemsInTime($behavior->filterUserPermission($itemCollection,$state), $state);
    }
    
    protected function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }

//    /**
//     * Set apps
//     *
//     * @param \Lugh\WebAppBundle\Entity\Apps $apps
//     * @return Accionista
//     */
//    public function setApps(\Lugh\WebAppBundle\Entity\Apps $apps)
//    {
//        $this->apps = $apps;
//
//        return $this;
//    }
//
//    /**
//     * Get apps
//     *
//     * @return \Lugh\WebAppBundle\Entity\Apps 
//     */
//    public function getApps()
//    {
//        return $this->apps;
//    }

    /**
     * Set ficheroAccionista
     *
     * @param \Lugh\WebAppBundle\Entity\FicheroAccionistas $ficheroAccionista
     * @return Accionista
     */
    public function setFicheroAccionista(\Lugh\WebAppBundle\Entity\FicheroAccionistas $ficheroAccionista = null)
    {
        $this->ficheroAccionista = $ficheroAccionista;

        return $this;
    }

    /**
     * Get ficheroAccionista
     *
     * @return \Lugh\WebAppBundle\Entity\FicheroAccionistas
     */
    public function getFicheroAccionista()
    {
        return $this->ficheroAccionista;
    }
    
    public function findLastAccion()
    {
        if (is_array($this->accion))
        {
            $actions = $this->accion;
            return end($actions);
        }
        return $this->accion->last();
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return Accionista
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string 
     */
    public function getTelephone()
    {
        return $this->telephone;
    }
    
    public function setDateTime($dateTime)
    {
        $this->itemAccionista->setDateTime($dateTime);

        return $this;
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"Apps"})
     * @SerializedName("apps")
     */
    
    public function getApps()
    {
        $apps = array();
        foreach ($this->getApp() as $app) {
            $apps[strtolower($app->getAppClass())] = $app->getState() == StateClass::statePublic;
        }
        return $apps;
    }
    
    /**
     * 
     * @VirtualProperty
     * @Groups({"Lives"})
     * @SerializedName("lives")
     */
    
    public function getLives()
    {
        $storage = $this->getContainer()->get('lugh.server')->getStorage();
        $lives = $storage->getLivesByAccionista($this);
        return $lives;
    }

    /**
     * Add app
     *
     * @param \Lugh\WebAppBundle\Entity\App $app
     * @return Accionista
     */
    public function addApp(\Lugh\WebAppBundle\Entity\App $app)
    {
        $app->setAccionista($this);
        $this->app[] = $app;

        return $this;
    }

    /**
     * Remove app
     *
     * @param \Lugh\WebAppBundle\Entity\App $app
     */
    public function removeApp(\Lugh\WebAppBundle\Entity\App $app)
    {
        $this->app->removeElement($app);
    }

    /**
     * Get app
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getApp()
    {
        return $this->app;
    }
    
    public function getAppbyDiscr($discr)
    {
        foreach ($this->getApp() as $app) {
            switch ($discr) {
                case 0:
                    if ($app::nameClass == 'AppVoto')
                        return $app;
                    break;
                case 1:
                    if ($app::nameClass == 'AppForo')
                        return $app;
                    break;
                case 2:
                    if ($app::nameClass == 'AppDerecho')
                        return $app;
                    break;
                case 3:
                    if ($app::nameClass == 'AppAV')
                        return $app;
                    break;

                default:
                    return null;
                    break;
            }
        }
        return null;
    }
    
    public function setLiveActive($idLive, $active)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->setAcitveLiveAccionista($this, $idLive, $active);
    }

    /**
     * Set acreditado
     *
     * @param boolean $acreditado
     * @return Accionista
     */
    public function setAcreditado($acreditado)
    {
        $this->acreditado = $acreditado;

        return $this;
    }

    /**
     * Get acreditado
     *
     * @return boolean 
     */
    public function getAcreditado()
    {
        return $this->acreditado;
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


    /**
     * Set receivedJson
     *
     * @param string $receivedJson
     * @return Accionista
     */
    public function setReceivedJson($receivedJson)
    {
        $this->receivedJson = $receivedJson;

        return $this;
    }

    /**
     * Get receivedJson
     *
     * @return string 
     */
    public function getReceivedJson()
    {
        return $this->receivedJson;
    }

    /**
     * Set validJson
     *
     * @param string $validJson
     * @return Accionista
     */
    public function setValidJson($validJson)
    {
        $this->validJson = $validJson;

        return $this;
    }

    /**
     * Get validJson
     *
     * @return string 
     */
    public function getValidJson()
    {
        return $this->validJson;
    }
    
    /**
     * Get acceso
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAllAccesoForFind($discr)
    {
        $acceso = array
            (
            'voto' => 'AccesoVoto',
            'foro' => 'AccesoForo',
            'derecho' => 'AccesoDerecho',
            'av' => 'AccesoAV'
        );
        
        $tipo = $acceso[$discr];
        return array_filter($this->getAccesos(), function($element)use($tipo){
            //var_dump($element::nameClass);
            return $element::nameClass == $tipo;
        });
    }
    
    /**
     * Add registros
     *
     * @param \Lugh\WebAppBundle\Entity\Registro $registros
     * @return Accionista
     */
    public function addRegistro(\Lugh\WebAppBundle\Entity\Registro $registros)
    {
        $this->registros[] = $registros;

        return $this;
    }

    /**
     * Remove registros
     *
     * @param \Lugh\WebAppBundle\Entity\Registro $registros
     */
    public function removeRegistro(\Lugh\WebAppBundle\Entity\Registro $registros)
    {
        $this->registros->removeElement($registros);
    }

    /**
     * Get registros
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRegistros()
    {
        return $this->registros;
    }

    /**
     * Add accesos
     *
     * @param \Lugh\WebAppBundle\Entity\Acceso $accesos
     * @return Accionista
     */
    public function addAcceso(\Lugh\WebAppBundle\Entity\Acceso $accesos)
    {
        $accesos->setAccionista($this);
        $this->accesos[] = $accesos;

        return $this;
    }

    /**
     * Remove accesos
     *
     * @param \Lugh\WebAppBundle\Entity\Acceso $accesos
     */
    public function removeAcceso(\Lugh\WebAppBundle\Entity\Acceso $accesos)
    {
        $this->accesos->removeElement($accesos);
    }

    /**
     * Get accesos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccesos()
    {
        return $this->restrictions($this->accesos);
    }

    /**
     * Set desertion
     *
     * @param \Lugh\WebAppBundle\Entity\Desertion $desertion
     * @return Accionista
     */
    public function setDesertion(\Lugh\WebAppBundle\Entity\Desertion $desertion = null)
    {
        $this->desertion = $desertion;

        return $this;
    }

    /**
     * Get desertion
     *
     * @return \Lugh\WebAppBundle\Entity\Desertion 
     */
    public function getDesertion()
    {
        return $this->desertion;
    }
}
